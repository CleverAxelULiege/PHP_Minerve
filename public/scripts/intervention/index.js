/**
 * Intervention Management System
 * 
 * This module handles the user interface for managing interventions, including:
 * - Loading and displaying intervention details
 * - Managing form population with intervention data
 * - Handling dynamic select options and breadcrumb interactions
 * - Managing side panel visibility and content
 * 
 * @author Your Name
 * @version 1.0.0
 */

import { formatDate } from "../helpers/date.js";
import { convertToAscii } from "../helpers/string.js";
import { InterventionApiCall } from "./api/InterventionApiCall.js";




/**
 * DOM Element References
 * Centralized collection of all DOM elements used throughout the application
 */
const DOM = {
    interventionTitleId: document.querySelector("#intervention_title a"),

    // User selection elements
    requesterUserList: document.getElementById("requester_user_list"),
    requesterUserInput: document.getElementById("requester_user"),
    requesterUserLink: document.querySelector(`[for="requester_user"] a`),
    requesterUserIdInput: document.getElementById("requester_user_id"),
    
    interventionTargetUserInput: document.getElementById("intervention_target_user"),
    interventionTargetUserIdInput: document.getElementById("intervention_target_user_id"),
    interventionTargetUserList: document.getElementById("intervention_target_user_list"),
    interventionTargetUserLink: document.querySelector(`[for="intervention_target_user"] a`),

    // Material and content elements
    materialInput: document.getElementById("material"),
    materialList: document.getElementById("material_list"),
    materialIdInput: document.getElementById("material_id"),
    materialLink: document.querySelector(`[for="material"] a`),

    // Date and metadata elements
    updatedAt: document.getElementById("updated_at"),
    createdAt: document.getElementById("created_at"),
    requestIp: document.getElementById("request_ip"),
    interventionDate: document.getElementById("intervention_date"),
    agendaDate: document.getElementById("agenda_date"),
    agendaComments: document.getElementById("agenda_comments"),

    // Form elements
    statusRadios: document.querySelectorAll("[name='status']"),
    problemDescription: document.getElementById("problem"),
    title: document.getElementById("title"),
    comments: document.getElementById("comments"),
    solution: document.getElementById("solution"),

    // Side panel elements
    interventionRows: document.querySelectorAll(".intervention_row"),
    interventionDetailsContainer: document.querySelector(".intervention_details_container"),
    interventionDetailsContent: document.querySelector(".intervention_details_container .content"),

    // Type and subtype selection
    interventionTypeSelect: document.querySelector("#intervention_type"),
    interventionSubtypeSelect: document.getElementById("intervention_subtype"),
    interventionSubtypeOptions: document.querySelectorAll("#intervention_subtype option"),

    // Breadcrumb elements
    breadcrumbKeywords: document.querySelector("#breadcrumb_keywords"),
    keywordSelect: document.querySelector("#keywords"),
    breadcrumbHelpers: document.querySelector("#breadcrumb_helpers"),
    helpersSelect: document.querySelector("#helpers")
};

/**
 * @type {Map<string, HTMLOptionsCollection>}
 */
const REQUESTER_USER_MAP = new Map();
const optionsRequesterUserList = Array.from(DOM.requesterUserList.children);
optionsRequesterUserList.forEach((option) => {
    if (option.value) {


        if (REQUESTER_USER_MAP.has(convertToAscii(option.value.trim().toUpperCase()))) {
            console.warn("A duplicate found with the same Ulg id, the same lastname, the same firstname : " + option.value.trim() + ".\nThe duplicate will be removed from the datalist.");
            
            DOM.requesterUserList.removeChild(option);
            return;
        }
        REQUESTER_USER_MAP.set(convertToAscii(option.value.trim().toUpperCase()), option);
    }
});

/**
 * @type {Map<string, HTMLOptionsCollection>}
 */
const INTERVENTION_TARGET_USER_MAP = new Map();
const optionsInterventionTargetUserList = Array.from(DOM.interventionTargetUserList.children);
optionsInterventionTargetUserList.forEach((option) => {
    if (option.value) {
        const normalized = convertToAscii(option.value.trim().toUpperCase());
        if (INTERVENTION_TARGET_USER_MAP.has(normalized)) {
            DOM.interventionTargetUserList.removeChild(option);
            return;
        }
        INTERVENTION_TARGET_USER_MAP.set(normalized, option);
    }
});

/**
 * @type {Map<string, HTMLOptionsCollection>}
 */
const MATERIAL_MAP = new Map();
const optionsMaterialList = Array.from(DOM.materialList.children);
optionsMaterialList.forEach((option) => {
    if (option.value) {
        const normalized = convertToAscii(option.value.trim().toUpperCase());
        if (MATERIAL_MAP.has(normalized)) {
            DOM.materialList.removeChild(option);
            return;
        }
        MATERIAL_MAP.set(normalized, option);
    }
});

/**
 * Application State Management
 * Tracks the current state of API requests and user interactions
 */
const AppState = {
    /**
     * Tracks the state of intervention detail requests
     */
    interventionRequest: {
        requestSent: false,
        responseReceived: false,
        abortController: new AbortController()
    }
};

/**
 * Configuration Constants
 */
const CONFIG = {
    SHOW_TIME_IN_DATES: true,
    BREADCRUMB_ITEM_CLASS: 'breadcrumb_item'
};

/**
 * Intervention Detail Management
 * Handles loading and displaying detailed intervention information
 */
class InterventionDetailManager {
    /**
     * Handles click events on intervention rows
     * @param {HTMLElement} row - The clicked intervention row
     */
    static handleRowClick(row) {
        this.setActiveRow(row);

        const interventionId = row.getAttribute("data-intervention-id");
        if (!interventionId) {
            throw new Error("InterventionId is NULL - ensure row has data-intervention-id attribute");
        }

        this.loadInterventionDetails(interventionId);
        this.showDetailsPanel();
    }

    /**
     * Sets the active state for the selected row
     * @param {HTMLElement} activeRow - The row to mark as active
     */
    static setActiveRow(activeRow) {
        DOM.interventionRows.forEach(row => row.classList.remove("active"));
        activeRow.classList.add("active");
    }

    /**
     * Loads intervention details from the API
     * @param {string|number} interventionId - The ID of the intervention to load
     */
    static async loadInterventionDetails(interventionId) {
        // Cancel any pending request
        if (AppState.interventionRequest.requestSent && !AppState.interventionRequest.responseReceived) {
            AppState.interventionRequest.abortController.abort();
        }

        // Update request state
        AppState.interventionRequest.requestSent = true;
        AppState.interventionRequest.responseReceived = false;
        AppState.interventionRequest.abortController = new AbortController();

        try {
            const data = await InterventionApiCall.getInterventionById(
                interventionId,
                AppState.interventionRequest.abortController.signal
            );

            if (data === null) {
                console.warn(`Failed to load intervention ${interventionId}: Data is null. Check if ID is valid integer or server-side issues.`);
                return;
            }

            FormPopulator.populateForm(data);
            DOM.interventionTitleId.textContent = `Intervention #${interventionId}`;
            DOM.interventionTitleId.innerHTML += `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z"/></svg>`
            setTimeout(() => {
                console.log(FormDataManager.getFormDataAsObject());

            }, 10);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error loading intervention details:', error);
            }
        }
    }

    /**
     * Shows the intervention details panel with smooth transition
     */
    static showDetailsPanel() {
        DOM.interventionDetailsContainer.classList.remove("hidden");
        DOM.interventionDetailsContainer.ontransitionend = () => {
            if (!DOM.interventionDetailsContainer.classList.contains("hidden")) {
                DOM.interventionDetailsContent.classList.remove("hidden");
            }
        };
    }
}

/**
 * Form Population and Management
 * Handles populating form fields with intervention data
 */
class FormPopulator {
    /**
     * Populates the entire form with intervention data
     * @param {Object} data - The intervention data object
     */
    static populateForm(data) {
        // Clear existing breadcrumbs
        this.clearBreadcrumbs();

        // Update request state
        AppState.interventionRequest.requestSent = false;
        AppState.interventionRequest.responseReceived = true;

        // Populate form fields
        this.populateDates(data);
        this.populateUsers(data);
        this.populateMaterial(data);
        this.populateInterventionTypes(data);
        this.populateBreadcrumbs(data);
        this.populateStatus(data);
        this.populateTextFields(data);
    }

    /**
     * Clears all breadcrumb containers
     */
    static clearBreadcrumbs() {
        DOM.breadcrumbHelpers.innerHTML = "";
        DOM.breadcrumbKeywords.innerHTML = "";
    }

    /**
     * Populates date fields
     * @param {Object} data - The intervention data
     */
    static populateDates(data) {
        if (data.requestDate) {
            DOM.createdAt.textContent = formatDate(new Date(data.requestDate), CONFIG.SHOW_TIME_IN_DATES);
        }

        if (data.updatedAt) {
            DOM.updatedAt.textContent = formatDate(new Date(data.updatedAt), CONFIG.SHOW_TIME_IN_DATES);
        }

        if (data.interventionDate) {
            DOM.interventionDate.textContent = formatDate(new Date(data.interventionDate), CONFIG.SHOW_TIME_IN_DATES);
        }
    }

    /**
     * Populates user selection fields
     * @param {Object} data - The intervention data
     */
    static populateUsers(data) {
        if (data.requesterUserId) {
            FormFieldHelper.setValueInInputFromListOptionId(
                DOM.requesterUserInput,
                DOM.requesterUserIdInput,
                DOM.requesterUserList,
                data.requesterUserId
            );

           DOM.requesterUserLink.setAttribute("href", data.requesterUserId);
        }

        if (data.targetUserId) {
            FormFieldHelper.setValueInInputFromListOptionId(
                DOM.interventionTargetUserInput,
                DOM.interventionTargetUserIdInput,
                DOM.interventionTargetUserList,
                data.targetUserId
            );

            DOM.interventionTargetUserLink.setAttribute("href", data.targetUserId);
        }
    }

    /**
     * Populates material selection field
     * @param {Object} data - The intervention data
     */
    static populateMaterial(data) {
        if (data.materialId) {
            FormFieldHelper.setValueInInputFromListOptionId(
                DOM.materialInput,
                DOM.materialIdInput,
                DOM.materialList,
                data.materialId
            );

            DOM.materialLink.setAttribute("href", data.materialId);
        }
    }

    /**
     * Populates intervention type and subtype fields
     * @param {Object} data - The intervention data
     */
    static populateInterventionTypes(data) {
        if (data.typeId) {
            const option = DOM.interventionTypeSelect.querySelector(`[value="${data.typeId}"]`);
            if (option) {
                option.selected = true;
                InterventionTypeManager.updateSubtypeOptions();
            }
        }

        if (data.subtypeId) {
            const option = DOM.interventionSubtypeSelect.querySelector(`[value="${data.subtypeId}"]`);
            if (option) {
                option.selected = true;
            }
        }
    }

    /**
     * Populates breadcrumb items (keywords and helpers)
     * @param {Object} data - The intervention data
     */
    static populateBreadcrumbs(data) {
        if (data.keywords) {
            data.keywords.forEach(keyword => {
                BreadcrumbManager.addBreadcrumbItem(keyword.name, keyword.id, DOM.breadcrumbKeywords);
            });
        }

        if (data.helpers) {
            data.helpers.forEach(helper => {
                BreadcrumbManager.addBreadcrumbItem(helper.surname, helper.id, DOM.breadcrumbHelpers);
            });
        }
    }

    /**
     * Populates status radio buttons
     * @param {Object} data - The intervention data
     */
    static populateStatus(data) {
        if (data.status) {
            DOM.statusRadios.forEach(status => {
                status.checked = (data.status == status.value);
            });
        }
    }

    /**
     * Populates text input and textarea fields
     * @param {Object} data - The intervention data
     */
    static populateTextFields(data) {
        if (data.description) {
            DOM.problemDescription.textContent = data.description;
            DOM.problemDescription.innerHTML = DOM.problemDescription.textContent.replace(/\r\n/g, "<br />");
        }

        if (data.title) {
            DOM.title.value = data.title;
        }

        if (data.comments) {
            DOM.comments.value = data.comments;
        }

        if (data.solution) {
            DOM.solution.value = data.solution;
        }
    }
}

/**
 * Breadcrumb Management
 * Handles adding and removing breadcrumb items for keywords and helpers
 */
class BreadcrumbManager {
    /**
     * Adds a new breadcrumb item to the specified container
     * @param {string} text - The display text for the breadcrumb
     * @param {string|number} value - The value to store in the hidden input
     * @param {HTMLElement} container - The container to add the breadcrumb to
     */
    static addBreadcrumbItem(text, value, container) {
        const item = document.createElement('div');
        item.className = CONFIG.BREADCRUMB_ITEM_CLASS;

        // Determine the input name based on container
        const inputName = container === DOM.breadcrumbKeywords ? 'keyword_ids[]' : 'helper_ids[]';

        item.innerHTML = `
      ${text}
      <input type="hidden" name="${inputName}" value="${value}">
      <span class="remove" onclick="BreadcrumbManager.removeBreadcrumbItem(this)">&times;</span>
    `;

        container.appendChild(item);
    }

    /**
     * Removes a breadcrumb item
     * @param {HTMLElement} element - The remove button element
     */
    static removeBreadcrumbItem(element) {
        element.parentElement.remove();
    }

    /**
     * Handles keyword select change events
     */
    static handleKeywordSelectChange() {
        if (DOM.keywordSelect.value === "") return;

        const keywordId = DOM.keywordSelect.value;
        const keywordText = DOM.keywordSelect.options[DOM.keywordSelect.selectedIndex].text;

        this.addBreadcrumbItem(keywordText, keywordId, DOM.breadcrumbKeywords);
        DOM.keywordSelect.value = ""; // Reset selection
    }

    /**
     * Handles helper select change events
     */
    static handleHelperSelectChange() {
        if (DOM.helpersSelect.value === "") return;

        const helperId = DOM.helpersSelect.value;
        const helperText = DOM.helpersSelect.options[DOM.helpersSelect.selectedIndex].text;

        this.addBreadcrumbItem(helperText, helperId, DOM.breadcrumbHelpers);
        DOM.helpersSelect.value = ""; // Reset selection
    }
}

/**
 * Intervention Type Management
 * Handles the dynamic filtering of subtypes based on selected type
 */
class InterventionTypeManager {
    /**
     * Updates the visibility of subtype options based on selected intervention type
     */
    static updateSubtypeOptions() {
        const interventionTypeId = DOM.interventionTypeSelect.value;

        DOM.interventionSubtypeOptions.forEach(option => {
            if (interventionTypeId === "") {
                option.style.display = "none";
            } else if (option.getAttribute("data-intervention-type-id") === interventionTypeId) {
                option.style.display = "";
            } else {
                option.style.display = "none";
            }

            // Reset to default option
            if (option.value === "") {
                option.selected = true;
            }
        });
    }
}

/**
 * Form Field Helper Utilities
 * Utility functions for working with form fields and data lists
 */
class FormFieldHelper {
    /**
     * Sets the value of an input based on a data list option ID
     * @param {HTMLInputElement} input - The input element to set
     * @param {HTMLInputElement} hiddenInput - The input element to set
     * @param {HTMLDataListElement} list - The data list to search
     * @param {string|number} id - The ID to match against data-value-id attribute
     */
    static setValueInInputFromListOptionId(input, hiddenInput, list, id) {
        const option = list.querySelector(`[data-value-id="${id}"]`);
        if (option) {
            input.value = option.value;
            hiddenInput.value = id;
        } else {
            console.warn(`Option with data-value-id="${id}" not found in list`);
        }
    }
}

/**
 * Event Listener Registration
 * Sets up all event listeners for the application
 */
class EventListeners {
    /**
     * Registers all event listeners
     */
    static registerAll() {
        // Intervention row click handlers
        DOM.interventionRows.forEach(row => {
            row.addEventListener("click", () => {
                InterventionDetailManager.handleRowClick(row);
            });
        });

        // Intervention type change handler
        DOM.interventionTypeSelect.addEventListener("change", () => {
            InterventionTypeManager.updateSubtypeOptions();
        });

        // Breadcrumb select handlers
        DOM.keywordSelect.addEventListener("change", () => {
            BreadcrumbManager.handleKeywordSelectChange();
        });

        DOM.helpersSelect.addEventListener("change", () => {
            BreadcrumbManager.handleHelperSelectChange();
        });

        // Autocomplete for requester user
        DOM.requesterUserInput.addEventListener("input", (e) => {
            const inputValue = convertToAscii(e.target.value.trim().toUpperCase());
            const registeredOption = REQUESTER_USER_MAP.get(inputValue);
            if (registeredOption) {
                DOM.requesterUserInput.value = registeredOption.getAttribute("value");
                DOM.requesterUserIdInput.value = registeredOption.getAttribute("data-value-id");
                DOM.requesterUserLink.setAttribute("href", DOM.requesterUserIdInput.value);
            } else {
                DOM.requesterUserIdInput.value = "";
            }
        });

        // Autocomplete for intervention target user
        DOM.interventionTargetUserInput.addEventListener("input", (e) => {
            const inputValue = convertToAscii(e.target.value.trim().toUpperCase());
            const registeredOption = INTERVENTION_TARGET_USER_MAP.get(inputValue);
            if (registeredOption) {
                DOM.interventionTargetUserInput.value = registeredOption.getAttribute("value");
                DOM.interventionTargetUserIdInput.value = registeredOption.getAttribute("data-value-id");
                DOM.interventionTargetUserLink.setAttribute("href", DOM.interventionTargetUserIdInput.value);
            } else {
                DOM.interventionTargetUserIdInput.value = "";
            }
        });

        // Autocomplete for material input
        DOM.materialInput.addEventListener("input", (e) => {
            const inputValue = convertToAscii(e.target.value.trim().toUpperCase());
            const registeredOption = MATERIAL_MAP.get(inputValue);
            if (registeredOption) {
                DOM.materialInput.value = registeredOption.getAttribute("value");
                DOM.materialIdInput.value = registeredOption.getAttribute("data-value-id");
                DOM.materialLink.setAttribute("href", DOM.materialIdInput.value);
            } else {
                DOM.materialIdInput.value = "";
            }
        });

    }
}

/**
 * Form Data Management
 * Handles collecting all form data into a FormData object
 */
class FormDataManager {
    /**
     * Gets all form inputs and creates a FormData object
     * @param {HTMLElement} [container=document] - Container to search for inputs (defaults to entire document)
     * @returns {FormData} FormData object with all form field values
     */
    static getFormData(container = document) {
        const formData = new FormData();
        const inputs = this.getAllFormInputs(container);

        inputs.forEach(input => {
            const name = input.name || input.id;
            if (!name || input.closest(".date_picker .calendar")) return; // Skip inputs without name or id

            const value = this.getInputValue(input);
            if (value !== null) {
                formData.append(name, value);
            }
        });

        return formData;
    }

    /**
     * Gets all form inputs from the container
     * @param {HTMLElement} container - Container to search for inputs
     * @returns {HTMLElement[]} Array of form input elements
     */
    static getAllFormInputs(container) {
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
    static getInputValue(input) {
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
    static getFormDataAsObject(container = document) {
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
    static debugFormData(container = document) {
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

    /**
     * Validates that all required fields have values
     * @param {HTMLElement} [container=document] - Container to search for inputs
     * @returns {Object} Validation result with isValid boolean and missing fields array
     */
    static validateRequiredFields(container = document) {
        const requiredInputs = container.querySelectorAll('input[required], select[required], textarea[required]');
        const missingFields = [];

        requiredInputs.forEach(input => {
            const value = this.getInputValue(input);
            if (!value) {
                missingFields.push({
                    name: input.name || input.id,
                    element: input,
                    label: this.getInputLabel(input)
                });
            }
        });

        return {
            isValid: missingFields.length === 0,
            missingFields: missingFields
        };
    }

    /**
     * Gets the label text for an input element
     * @param {HTMLElement} input - The input element
     * @returns {string} The label text or input name/id
     */
    static getInputLabel(input) {
        // Try to find associated label
        const label = document.querySelector(`label[for="${input.id}"]`) ||
            input.closest('label') ||
            input.previousElementSibling?.tagName === 'LABEL' ? input.previousElementSibling : null;

        return label?.textContent?.trim() || input.name || input.id || 'Unknown field';
    }
}

/**
 * Application Initialization
 * Sets up the application when the DOM is ready
 */
class App {
    /**
     * Initializes the application
     */
    static init() {
        EventListeners.registerAll();
        InterventionTypeManager.updateSubtypeOptions(); // Initialize subtype options

        // Make BreadcrumbManager globally available for onclick handlers
        window.BreadcrumbManager = BreadcrumbManager;

        // Make FormDataManager globally available for easy access
        window.FormDataManager = FormDataManager;

        console.log('Intervention Management System initialized');
    }
}

// Initialize the application
App.init();


// Export classes for potential external use
export {
    InterventionDetailManager,
    FormPopulator,
    BreadcrumbManager,
    InterventionTypeManager,
    FormFieldHelper,
    FormDataManager
};