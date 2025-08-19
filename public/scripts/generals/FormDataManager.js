export class FormDataManager {
    /**
     * Gets all form inputs and creates a FormData object
     * @param {HTMLElement} [container=document] - Container to search for inputs (defaults to entire document)
     * @returns {FormData} FormData object with all form field values
     */
    getFormData(container = document) {
        const formData = new FormData();
        const inputs = this.getAllFormInputs(container);
        console.log(inputs);


        inputs.forEach(input => {
            const name = input.name || input.id;
            if (!name || input.closest(".date_picker .calendar")) return; // Skip inputs without name or id

            const value = this.getInputValue(input);
            formData.append(name, value);
        });

        return formData;
    }

    /**
     * Gets all form inputs from the container
     * @param {HTMLElement} container - Container to search for inputs
     * @returns {HTMLElement[]} Array of form input elements
     */
    getAllFormInputs(container) {
        const selectors = [
            'input[type="text"]',
            'input[type="hidden"]',
            'input[type="email"]',
            'input[type="number"]',
            'input[type="tel"]',
            'input[type="url"]',
            'input[type="password"]',
            'input[type="date"]',
            'input[type="datetime-local"]',
            'input[type="time"]',
            'input[type="radio"]:checked',
            'input[type="checkbox"]:checked',
            'select',
            'textarea'
        ];

        return Array.from(container.querySelectorAll(selectors.join(', ')));
    }

    /**
     * Gets the value from different types of form inputs
     * @param {HTMLElement} input - The input element
     * @returns {string|null} The input value or null if empty/invalid
     */
    getInputValue(input) {
        switch (input.type) {
            case 'radio':
            case 'checkbox':
                return input.checked ? input.value : null;
            case 'select-one':
            case 'select-multiple':
                return input.value || null;
            case 'textarea':
                return input.value.trim() || null;
            default:
                return input.value.trim() || null;
        }
    }

    /**
     * Gets form data as a plain JavaScript object
     * @param {HTMLElement} [container=document] - Container to search for inputs
     * @returns {Object} Plain object with form field names as keys and values
     */
    getFormDataAsObject(container = document) {
        const formData = this.getFormData(container);
        const obj = {};

        for (const [key, value] of formData.entries()) {
            // Handle array-like names (e.g., "keyword_ids[]")
            if (key.endsWith('[]')) {
                const arrayKey = key.slice(0, -2);
                if (!obj[arrayKey]) {
                    obj[arrayKey] = [];
                }
                obj[arrayKey].push(value);
            } else {
                obj[key] = value;
            }
        }

        return obj;
    }

    /**
     * Logs all form data to console for debugging
     * @param {HTMLElement} [container=document] - Container to search for inputs
     */
    debugFormData(container = document) {
        const formData = this.getFormData(container);
        const obj = this.getFormDataAsObject(container);

        console.group('Form Data Debug');
        console.log('FormData object:', formData);
        console.log('As plain object:', obj);
        console.log('Form entries:');

        for (const [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }

        console.groupEnd();

        return { formData, object: obj };
    }
}