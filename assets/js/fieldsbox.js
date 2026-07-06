/**
 * Fieldsbox front-end behaviour: conditional show/hide logic and tab
 * switching. Vanilla JS, no dependencies - enqueued once by
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

    /** Tab groups that already have click listeners bound, so refresh() doesn't double-bind them. */
    var initializedTabs = new WeakSet();

    /**
     * Read the current value of a field by name from within a container.
     *
     * Handles the different ways each field type exposes its value:
     * checkbox groups (multiple checked boxes), single checkboxes, radio
     * groups, native multi-selects, and plain inputs/selects/textareas.
     *
     * @param {Element} container Container element to search within.
     * @param {string} name Field name (without any "[]" suffix).
     * @returns {string|string[]|null} Current value, or null if no matching field exists.
     */
    function getFieldValue(container, name) {
        // A multiselect's inputs are named "name[]"; everything else is just "name".
        var els = container.querySelectorAll('[name="' + name + '"], [name="' + name + '[]"]');

        if (! els.length) {
            return null;
        }

        if (els[0].type === 'checkbox') {
            // Checkbox groups are only used by the multiselect field type's
            // sibling rendering; a lone checkbox is CheckboxField's boolean toggle.
            if (els.length > 1) {
                var values = [];
                els.forEach(function (el) {
                    if (el.checked) {
                        values.push(el.value);
                    }
                });
                return values;
            }
            return els[0].checked ? els[0].value : '';
        }

        if (els[0].type === 'radio') {
            var checkedEl = container.querySelector('[name="' + name + '"]:checked');
            return checkedEl ? checkedEl.value : '';
        }

        if (els[0].multiple) {
            return Array.prototype.slice.call(els[0].selectedOptions).map(function (option) {
                return option.value;
            });
        }

        return els[0].value;
    }

    /**
     * Evaluate a single conditional-logic rule against the current form state.
     *
     * @param {Element} container Container the rule's target field lives in.
     * @param {{field: string, value: *, operator?: string}} rule
     * @returns {boolean} Whether the rule currently matches.
     */
    function ruleMatches(container, rule) {
        var value = getFieldValue(container, rule.field);
        var operator = rule.operator || '=';

        // Multiselect/checkbox-group values are arrays - treat the rule as
        // "does the target value appear among the selected values".
        if (Array.isArray(value)) {
            var has = value.indexOf(String(rule.value)) !== -1;
            return operator === '!=' ? ! has : has;
        }

        value = value === null ? '' : String(value);
        var target = rule.value === undefined ? '' : String(rule.value);

        switch (operator) {
            case '!=':
                return value !== target;
            case 'contains':
                return value.indexOf(target) !== -1;
            case 'not_contains':
                return value.indexOf(target) === -1;
            case 'empty':
                return value === '';
            case 'not_empty':
                return value !== '';
            default:
                return value === target;
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

        var data = JSON.parse(config);
        var results = data.rules.map(function (rule) {
            return ruleMatches(container, rule);
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
     * Fully initialize (or re-scan) a single container: evaluate its
     * conditional fields, bind any tab groups inside it, and - the first
     * time only - bind the change/input listeners that keep conditional
     * logic live as the user edits the form.
     *
     * @param {Element} container
     */
    function bindContainer(container) {
        evaluateContainer(container);
        container.querySelectorAll('.fieldsbox-tabs').forEach(bindTabs);

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
     * Public API: (re)scan a container for conditional-logic fields and tab
     * groups, or every .fieldsbox-container on the page when called with no
     * argument. Safe to call repeatedly.
     *
     * Call this after injecting new field markup at runtime (e.g. an
     * AJAX-loaded repeater row) so it behaves the same as markup that was
     * already present on page load:
     *
     *   document.dispatchEvent(...); // your code adds a .fieldsbox-container to the DOM
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
