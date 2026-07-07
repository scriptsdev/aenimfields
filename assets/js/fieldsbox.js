/**
 * Fieldsbox front-end behaviour: conditional show/hide logic, tab
 * switching, media pickers, the map field, date/time pickers, and repeater
 * rows. Vanilla JS, no dependencies beyond what WordPress core or the field
 * itself already loads (wp.media, Leaflet, Flatpickr) - enqueued once by
 * Fieldsbox::enqueue_assets() and shared by every container on the page.
 *
 * Exposes a small public API on window.Fieldsbox so consuming plugins can
 * re-scan markup they inject after page load (e.g. an AJAX-loaded repeater
 * row) - see refresh() below.
 */
(function (global) {
    'use strict';

    /** Containers that already have change/input listeners bound, so refresh() doesn't double-bind them. */
    var initializedContainers = new WeakSet();

    /** Tab groups that already have click listeners bound. */
    var initializedTabs = new WeakSet();

    /** Media (image/file/gallery) fields that already have their picker wired up. */
    var initializedMedia = new WeakSet();

    /** Map fields that already have a Leaflet instance. */
    var initializedMaps = new WeakSet();

    /** Repeaters that already have their add/remove behaviour wired up. */
    var initializedRepeaters = new WeakSet();

    /** Date/date-time/time fields that already have a Flatpickr instance. */
    var initializedFlatpickr = new WeakSet();

    /**
     * Read the current value of a field within a given scope element.
     *
     * Looks the field up by its wrapper's data-field-name (the logical
     * name set by Field::render()) rather than by HTML "name", since
     * repeater rows give each sub-field a unique, index-namespaced name.
     *
     * @param {Element} scope Container, repeater row, or group element to search within.
     * @param {string} name Logical field name (Field::get_name()), with any "parent." prefix already stripped.
     * @returns {string|string[]|null} Current value, or null if no matching field exists in scope.
     */
    function getFieldValue(scope, name) {
        var wrapper = scope.querySelector('[data-field-name="' + name + '"]');

        if (! wrapper) {
            return null;
        }

        var checkboxes = wrapper.querySelectorAll('input[type="checkbox"]');
        if (checkboxes.length > 1) {
            var values = [];
            checkboxes.forEach(function (el) {
                if (el.checked) {
                    values.push(el.value);
                }
            });
            return values;
        }
        if (checkboxes.length === 1) {
            return checkboxes[0].checked ? checkboxes[0].value : '';
        }

        var checkedRadio = wrapper.querySelector('input[type="radio"]:checked');
        if (checkedRadio) {
            return checkedRadio.value;
        }
        if (wrapper.querySelector('input[type="radio"]')) {
            return '';
        }

        var select = wrapper.querySelector('select');
        if (select) {
            if (select.multiple) {
                return Array.prototype.slice.call(select.selectedOptions).map(function (option) {
                    return option.value;
                });
            }
            return select.value;
        }

        var input = wrapper.querySelector('input, textarea');
        return input ? input.value : null;
    }

    /**
     * Build the chain of scopes a field can reach for conditional logic,
     * innermost first: chain[0] is the field's own row/group (its
     * siblings), chain[1] is one "parent." level up, chain[2] two levels
     * up, and so on, ending with the top-level container.
     *
     * @param {Element} field
     * @param {Element} container
     * @returns {Element[]}
     */
    function buildScopeChain(field, container) {
        var chain = [];
        var current = field.closest('.fieldsbox-repeater-row, .fieldsbox-group') || container;

        while (true) {
            chain.push(current);

            if (current === container || ! current.parentElement) {
                break;
            }

            current = current.parentElement.closest('.fieldsbox-repeater-row, .fieldsbox-group') || container;
        }

        return chain;
    }

    /**
     * Strip any leading "parent." segments off a rule's field name and
     * resolve which scope in the chain they point to - e.g. "parent.parent.x"
     * resolves to chain[2] (or the outermost scope if the nesting isn't
     * actually that deep).
     *
     * @param {Element[]} chain From buildScopeChain().
     * @param {string} fieldName Raw rule.field, e.g. "parent.parent.crb_in_production".
     * @returns {{scope: Element, name: string}}
     */
    function resolveRuleScope(chain, fieldName) {
        var depth = 0;

        while (fieldName.indexOf('parent.') === 0) {
            fieldName = fieldName.slice('parent.'.length);
            depth++;
        }

        return {
            scope: chain[Math.min(depth, chain.length - 1)],
            name: fieldName,
        };
    }

    /**
     * Evaluate a single conditional-logic rule against the current form
     * state. Mirrors Carbon Fields' rule shape: field/value/compare, with
     * compare one of =, <, >, <=, >=, IN, NOT IN, INCLUDES, EXCLUDES.
     *
     * @param {Element[]} chain From buildScopeChain(), for resolving "parent." prefixes.
     * @param {{field: string, value?: *, compare?: string}} rule
     * @returns {boolean} Whether the rule currently matches.
     */
    function ruleMatches(chain, rule) {
        var resolved = resolveRuleScope(chain, rule.field);
        var value = getFieldValue(resolved.scope, resolved.name);
        var compare = rule.compare || '=';
        var target = rule.value === undefined ? '' : rule.value;

        // A checkbox/toggle's actual stored value is '1'/'' - accept PHP's
        // true/false (as used in Carbon Fields examples) as aliases for that.
        if (target === true) {
            target = '1';
        } else if (target === false) {
            target = '';
        }

        var targetList = (Array.isArray(target) ? target : [target]).map(String);

        // Multiselect/checkbox-group values are arrays - only
        // IN/NOT IN/INCLUDES/EXCLUDES make sense against them.
        if (Array.isArray(value)) {
            switch (compare) {
                case 'NOT IN':
                    return ! targetList.some(function (v) { return value.indexOf(v) !== -1; });
                case 'EXCLUDES':
                    return targetList.every(function (v) { return value.indexOf(v) === -1; });
                case 'INCLUDES':
                    return targetList.every(function (v) { return value.indexOf(v) !== -1; });
                case 'IN':
                default:
                    return targetList.some(function (v) { return value.indexOf(v) !== -1; });
            }
        }

        value = value === null ? '' : String(value);

        switch (compare) {
            case 'IN':
                return targetList.indexOf(value) !== -1;
            case 'NOT IN':
                return targetList.indexOf(value) === -1;
            case '<':
                return parseFloat(value) < parseFloat(target);
            case '>':
                return parseFloat(value) > parseFloat(target);
            case '<=':
                return parseFloat(value) <= parseFloat(target);
            case '>=':
                return parseFloat(value) >= parseFloat(target);
            default: // '='
                return value === String(target);
        }
    }

    /**
     * Show or hide a single field wrapper based on its data-conditional
     * rule set (written by Field::render() as JSON).
     *
     * @param {Element} container
     * @param {Element} field Field wrapper element carrying the data-conditional attribute.
     */
    function evaluateField(container, field) {
        var config = field.getAttribute('data-conditional');

        if (! config) {
            return;
        }

        var chain = buildScopeChain(field, container);
        var data = JSON.parse(config);
        var results = data.rules.map(function (rule) {
            return ruleMatches(chain, rule);
        });

        // 'OR' relation passes if any rule matches; 'AND' (default) requires all of them.
        var visible = data.relation === 'OR' ? results.some(Boolean) : results.every(Boolean);
        field.style.display = visible ? '' : 'none';
    }

    /**
     * Re-evaluate every conditionally-shown field within a container.
     *
     * @param {Element} container
     */
    function evaluateContainer(container) {
        container.querySelectorAll('[data-conditional]').forEach(function (field) {
            evaluateField(container, field);
        });
    }

    /**
     * Wire up click-to-switch behaviour for a single .fieldsbox-tabs block.
     * Idempotent - safe to call again on a tab group that's already bound.
     *
     * @param {Element} tabs
     */
    function bindTabs(tabs) {
        if (initializedTabs.has(tabs)) {
            return;
        }
        initializedTabs.add(tabs);

        tabs.querySelectorAll('.fieldsbox-tab-link').forEach(function (link) {
            link.addEventListener('click', function () {
                var target = link.getAttribute('data-tab');

                tabs.querySelectorAll('.fieldsbox-tab-link').forEach(function (otherLink) {
                    otherLink.classList.toggle('is-active', otherLink === link);
                });
                tabs.querySelectorAll('.fieldsbox-tab-panel').forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target);
                });
            });
        });
    }

    /**
     * Wire up the "Select"/"Remove" buttons on a single image/file field to
     * the WordPress media library modal (wp.media). Idempotent.
     *
     * @param {Element} field
     */
    function initMediaField(field) {
        if (initializedMedia.has(field) || typeof wp === 'undefined' || ! wp.media) {
            return;
        }
        initializedMedia.add(field);

        var type = field.getAttribute('data-media-type');
        var input = field.querySelector('.fieldsbox-media-value');
        var preview = field.querySelector('.fieldsbox-media-preview');
        var selectBtn = field.querySelector('.fieldsbox-media-select');
        var removeBtn = field.querySelector('.fieldsbox-media-remove');

        selectBtn.addEventListener('click', function (e) {
            e.preventDefault();

            var frame = wp.media({
                title: type === 'image' ? 'Select Image' : 'Select File',
                multiple: false,
                library: type === 'image' ? { type: 'image' } : {},
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                input.value = attachment.id;

                // Built via the DOM rather than an innerHTML string, so
                // attachment.filename/url (data from the media library, not
                // this page's own markup) can never be parsed as HTML.
                preview.innerHTML = '';

                if (type === 'image') {
                    var src = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                    var img = document.createElement('img');
                    img.src = src;
                    img.alt = '';
                    preview.appendChild(img);
                } else {
                    var link = document.createElement('a');
                    link.href = attachment.url;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.textContent = attachment.filename;
                    preview.appendChild(link);
                }

                preview.style.display = '';
                if (removeBtn) {
                    removeBtn.style.display = '';
                }
            });

            frame.open();
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                input.value = '';
                preview.innerHTML = '';
                preview.style.display = 'none';
                removeBtn.style.display = 'none';
            });
        }
    }

    /**
     * Wire up the "Add Images" button and per-thumbnail remove buttons on a
     * gallery field to wp.media, keeping its CSV hidden input in sync.
     * Idempotent.
     *
     * @param {Element} field
     */
    function initGalleryField(field) {
        if (initializedMedia.has(field) || typeof wp === 'undefined' || ! wp.media) {
            return;
        }
        initializedMedia.add(field);

        var input = field.querySelector('.fieldsbox-media-value');
        var itemsWrap = field.querySelector('.fieldsbox-gallery-items');
        var addBtn = field.querySelector('.fieldsbox-gallery-add');

        function currentIds() {
            return input.value ? input.value.split(',').filter(Boolean) : [];
        }

        function addItem(attachment) {
            var item = document.createElement('div');
            item.className = 'fieldsbox-gallery-item';
            item.setAttribute('data-id', attachment.id);

            // Built via the DOM rather than an innerHTML string - see the
            // same reasoning in initMediaField() above.
            var thumb = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
            var thumbImg = document.createElement('img');
            thumbImg.src = thumb;
            thumbImg.alt = '';

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'fieldsbox-gallery-remove';
            removeBtn.textContent = '×';

            item.appendChild(thumbImg);
            item.appendChild(removeBtn);

            itemsWrap.appendChild(item);
        }

        itemsWrap.addEventListener('click', function (e) {
            if (! e.target.classList.contains('fieldsbox-gallery-remove')) {
                return;
            }

            var item = e.target.closest('.fieldsbox-gallery-item');
            var id = item.getAttribute('data-id');
            input.value = currentIds().filter(function (existingId) {
                return existingId !== id;
            }).join(',');
            item.remove();
        });

        addBtn.addEventListener('click', function (e) {
            e.preventDefault();

            var frame = wp.media({
                title: 'Add Images',
                multiple: true,
                library: { type: 'image' },
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection');
                var ids = currentIds();

                selection.each(function (attachment) {
                    attachment = attachment.toJSON();
                    if (ids.indexOf(String(attachment.id)) === -1) {
                        ids.push(String(attachment.id));
                        addItem(attachment);
                    }
                });

                input.value = ids.join(',');
            });

            frame.open();
        });
    }

    /**
     * Initialize Flatpickr on a single date/date-time/time input. The mode
     * and format come from data attributes written by
     * AbstractFlatpickrField::render_input() so PHP stays the single source
     * of truth for what format is being parsed/stored. No-ops until the
     * Flatpickr script (enqueued alongside fieldsbox.js) has loaded.
     * Idempotent.
     *
     * @param {Element} field
     */
    function initFlatpickr(field) {
        if (initializedFlatpickr.has(field) || typeof flatpickr === 'undefined') {
            return;
        }
        initializedFlatpickr.add(field);

        var mode = field.getAttribute('data-flatpickr-mode');
        var extraOptions = field.getAttribute('data-flatpickr-options');
        var options = extraOptions ? JSON.parse(extraOptions) : {};

        options.dateFormat = field.getAttribute('data-flatpickr-format');

        if (mode === 'datetime') {
            options.enableTime = true;
        } else if (mode === 'time') {
            options.enableTime = true;
            options.noCalendar = true;
        }

        flatpickr(field, options);
    }

    /**
     * Initialize a Leaflet map with a draggable pin for a single map field.
     * No-ops until the Leaflet script (enqueued alongside fieldsbox.js) has
     * loaded. Idempotent.
     *
     * @param {Element} field
     */
    function initMap(field) {
        if (initializedMaps.has(field) || typeof L === 'undefined') {
            return;
        }
        initializedMaps.add(field);

        var canvas = field.querySelector('.fieldsbox-map-canvas');
        var latInput = field.querySelector('.fieldsbox-map-lat');
        var lngInput = field.querySelector('.fieldsbox-map-lng');
        var locateBtn = field.querySelector('.fieldsbox-map-locate');

        var lat = parseFloat(latInput.value) || parseFloat(field.getAttribute('data-default-lat'));
        var lng = parseFloat(lngInput.value) || parseFloat(field.getAttribute('data-default-lng'));
        var zoom = parseInt(field.getAttribute('data-default-zoom'), 10) || 13;

        var map = L.map(canvas).setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        function setPosition(newLat, newLng) {
            latInput.value = newLat;
            lngInput.value = newLng;
            marker.setLatLng([newLat, newLng]);
        }

        marker.on('dragend', function () {
            var pos = marker.getLatLng();
            setPosition(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            setPosition(e.latlng.lat, e.latlng.lng);
        });

        if (locateBtn) {
            locateBtn.addEventListener('click', function () {
                if (! navigator.geolocation) {
                    return;
                }

                navigator.geolocation.getCurrentPosition(function (position) {
                    setPosition(position.coords.latitude, position.coords.longitude);
                    map.setView([position.coords.latitude, position.coords.longitude], zoom);
                });
            });
        }
    }

    /**
     * Wire up "Add Row"/"Remove" behaviour for a single repeater field.
     * New rows are created by cloning the field's <template> and replacing
     * the "__INDEX__" placeholder in every name/id/for attribute with a
     * fresh, page-unique index - see RepeaterField's class doc comment for
     * why a real index is used instead of empty-bracket names. Idempotent.
     *
     * @param {Element} repeater
     */
    function initRepeater(repeater) {
        if (initializedRepeaters.has(repeater)) {
            return;
        }
        initializedRepeaters.add(repeater);

        var rowsWrap = repeater.querySelector('.fieldsbox-repeater-rows');
        var template = repeater.querySelector('.fieldsbox-repeater-template');
        var addBtn = repeater.querySelector('.fieldsbox-repeater-add');
        var maxRows = parseInt(repeater.getAttribute('data-max-rows'), 10) || null;
        var nextIndex = Date.now();

        function bindRow(row) {
            var removeBtn = row.querySelector('.fieldsbox-repeater-remove');

            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    row.remove();
                });
            }

            bindContainer(row);
        }

        rowsWrap.querySelectorAll('.fieldsbox-repeater-row').forEach(bindRow);

        if (addBtn && template) {
            addBtn.addEventListener('click', function () {
                if (maxRows && rowsWrap.children.length >= maxRows) {
                    return;
                }

                var index = nextIndex++;
                var fragment = template.content.cloneNode(true);

                fragment.querySelectorAll('[name], [id], label[for]').forEach(function (el) {
                    ['name', 'id', 'for'].forEach(function (attr) {
                        if (el.hasAttribute(attr)) {
                            // Case-insensitive: sanitize_key() on the PHP side lowercases
                            // id/for attributes, so they carry "__index__", not "__INDEX__"
                            // like the name attribute does.
                            el.setAttribute(attr, el.getAttribute(attr).replace(/__INDEX__/gi, index));
                        }
                    });
                });

                var row = fragment.querySelector('.fieldsbox-repeater-row');
                rowsWrap.appendChild(fragment);
                bindRow(row);
            });
        }
    }

    /**
     * Fully initialize (or re-scan) a single container, row, or group: run
     * conditional-logic evaluation, bind tabs/media/map/date-picker/repeater
     * behaviour inside it, and - the first time only - bind the
     * change/input listeners that keep conditional logic live as the user
     * edits the form.
     *
     * @param {Element} container
     */
    function bindContainer(container) {
        evaluateContainer(container);
        container.querySelectorAll('.fieldsbox-tabs').forEach(bindTabs);
        container.querySelectorAll('[data-media-type="image"], [data-media-type="file"]').forEach(initMediaField);
        container.querySelectorAll('[data-media-type="gallery"]').forEach(initGalleryField);
        container.querySelectorAll('.fieldsbox-map-field').forEach(initMap);
        container.querySelectorAll('.fieldsbox-flatpickr').forEach(initFlatpickr);
        container.querySelectorAll('.fieldsbox-repeater').forEach(initRepeater);

        if (initializedContainers.has(container)) {
            return;
        }
        initializedContainers.add(container);

        container.addEventListener('change', function () {
            evaluateContainer(container);
        });
        container.addEventListener('input', function () {
            evaluateContainer(container);
        });
    }

    /**
     * Public API: (re)scan a container for conditional-logic fields, tabs,
     * media pickers, maps, and repeaters, or every .fieldsbox-container on
     * the page when called with no argument. Safe to call repeatedly.
     *
     * Call this after injecting new field markup at runtime (e.g. an
     * AJAX-loaded meta box) so it behaves the same as markup that was
     * already present on page load:
     *
     *   // your code adds a .fieldsbox-container to the DOM
     *   Fieldsbox.refresh(newContainerEl);
     *
     * @param {Element} [container] Specific container to (re)scan; omit to scan the whole page.
     */
    function refresh(container) {
        if (container) {
            bindContainer(container);
            return;
        }

        document.querySelectorAll('.fieldsbox-container').forEach(bindContainer);
    }

    document.addEventListener('DOMContentLoaded', function () {
        refresh();
    });

    global.Fieldsbox = {
        refresh: refresh,
    };
})(window);
