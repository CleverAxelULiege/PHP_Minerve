import { FormDataManager } from "../generals/FormDataManager.js";
import { ColorHelper } from "../helpers/ColorHelper.js";
import { formatDate } from "../helpers/date.js";
import { convertToAscii } from "../helpers/string.js";
import { InterventionApiCall } from "./api/InterventionApiCall.js";


const CONFIG = {
    SHOW_TIME_IN_DATES: true,
    BREADCRUMB_ITEM_CLASS: 'breadcrumb_item'
};

export class InterventionFormManager {
    /**
     * @param {HTMLElement} rootElement 
     */
    constructor(rootElement) {
        this.formDataManager = new FormDataManager();
        this.rootElement = rootElement;
        this.DOM = {
            interventionTitleId: rootElement.querySelector("#intervention_title a"),

            // User selection elements
            requesterUserList: rootElement.querySelector("#requester_user_list"),
            requesterUserInput: rootElement.querySelector("#requester_user"),
            requesterUserLink: rootElement.querySelector(`[for="requester_user"] a`),
            requesterUserIdInput: rootElement.querySelector("#requester_user_id"),

            interventionTargetUserInput: rootElement.querySelector("#intervention_target_user"),
            interventionTargetUserIdInput: rootElement.querySelector("#intervention_target_user_id"),
            interventionTargetUserList: rootElement.querySelector("#intervention_target_user_list"),
            interventionTargetUserLink: rootElement.querySelector(`[for="intervention_target_user"] a`),

            // Material and content elements
            materialInput: rootElement.querySelector("#material"),
            materialList: rootElement.querySelector("#material_list"),
            materialIdInput: rootElement.querySelector("#material_id"),
            materialLink: rootElement.querySelector(`[for="material"] a`),

            // Date and metadata elements
            updatedAt: rootElement.querySelector("#updated_at"),
            createdAt: rootElement.querySelector("#created_at"),
            requestIp: rootElement.querySelector("#request_ip"),
            interventionDate: rootElement.querySelector("#intervention_date"),
            agendaDate: rootElement.querySelector("#agenda_date"),
            agendaComments: rootElement.querySelector("#agenda_comments"),

            // Form elements
            statusRadios: rootElement.querySelectorAll("[name='status']"),
            problemDescription: rootElement.querySelector("#problem"),
            title: rootElement.querySelector("#title"),
            comments: rootElement.querySelector("#comments"),
            solution: rootElement.querySelector("#solution"),

            // Type and subtype selection
            interventionTypeSelect: rootElement.querySelector("#intervention_type"),
            interventionSubtypeSelect: rootElement.querySelector("#intervention_subtype"),
            interventionSubtypeOptions: rootElement.querySelectorAll("#intervention_subtype option"),

            // Breadcrumb elements
            breadcrumbKeywords: rootElement.querySelector("#breadcrumb_keywords"),
            keywordSelect: rootElement.querySelector("#keywords"),
            breadcrumbHelpers: rootElement.querySelector("#breadcrumb_helpers"),
            helpersSelect: rootElement.querySelector("#helpers"),

            messagesContainer: rootElement.querySelector(".messages_container")
        };




        /**
         * @type {Map<string, HTMLOptionsCollection>}
         */
        this.requesterUserMap = new Map();
        const optionsRequesterUserList = Array.from(this.DOM.requesterUserList.children);
        optionsRequesterUserList.forEach((option) => {
            if (option.value) {


                if (this.requesterUserMap.has(convertToAscii(option.value.trim().toUpperCase()))) {
                    console.warn("A duplicate found with the same Ulg id, the same lastname, the same firstname : " + option.value.trim() + ".\nThe duplicate will be removed from the datalist.");

                    this.DOM.requesterUserList.removeChild(option);
                    return;
                }
                this.requesterUserMap.set(convertToAscii(option.value.trim().toUpperCase()), option);
            }
        });

        /**
         * @type {Map<string, HTMLOptionsCollection>}
         */
        this.interventionUserTargetMap = new Map();
        const optionsInterventionTargetUserList = Array.from(this.DOM.interventionTargetUserList.children);
        optionsInterventionTargetUserList.forEach((option) => {
            if (option.value) {


                if (this.interventionUserTargetMap.has(convertToAscii(option.value.trim().toUpperCase()))) {
                    this.DOM.interventionTargetUserList.removeChild(option);
                    return;
                }
                this.interventionUserTargetMap.set(convertToAscii(option.value.trim().toUpperCase()), option);
            }
        });

        /**
         * @type {Map<string, HTMLOptionsCollection>}
         */
        this.materialMap = new Map();
        const optionMapList = Array.from(this.DOM.materialList.children);
        optionMapList.forEach((option) => {
            if (option.value) {
                if (this.materialMap.has(convertToAscii(option.value.trim().toUpperCase()))) {
                    this.DOM.materialList.removeChild(option);
                    return;
                }
                this.materialMap.set(convertToAscii(option.value.trim().toUpperCase()), option);
            }
        });


        this.interventionRequest = {
            requestSent: false,
            responseReceived: false,
            abortController: new AbortController()
        }


        this.registerEvents();

    }

    async loadInterventionDetails(interventionId) {
        // Cancel any pending request
        if (this.interventionRequest.requestSent && !this.interventionRequest.responseReceived) {
            this.interventionRequest.abortController.abort();
        }

        // Update request state
        this.interventionRequest.requestSent = true;
        this.interventionRequest.responseReceived = false;
        this.interventionRequest.abortController = new AbortController();

        try {
            const data = await InterventionApiCall.getInterventionById(
                interventionId,
                this.interventionRequest.abortController.signal
            );

            if (data === null) {
                console.warn(`Failed to load intervention ${interventionId}: Data is null. Check if ID is valid integer or server-side issues.`);
                return;
            }
            this.populateForm(data);
            this.DOM.interventionTitleId.textContent = `Intervention #${interventionId}`;
            this.DOM.interventionTitleId.innerHTML += `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z"/></svg>`
            // setTimeout(() => {
            //     console.log(this.formDataManager.getFormData(this.rootElement));

            // }, 10);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error loading intervention details:', error);
            }
        }
    }

    populateForm(data) {
        // Clear existing breadcrumbs
        this.clearBreadcrumbs();

        // Update request state
        this.interventionRequest.requestSent = false;
        this.interventionRequest.responseReceived = true;

        // Populate form fields
        this.populateDates(data);
        this.populateUsers(data);
        this.populateMaterial(data);
        this.populateInterventionTypes(data);
        this.populateBreadcrumbs(data);
        this.populateStatus(data);
        this.populateTextFields(data);
        this.populateMessages(data);
    }

    clearBreadcrumbs() {
        this.DOM.breadcrumbHelpers.innerHTML = "";
        this.DOM.breadcrumbKeywords.innerHTML = "";
    }

    /**
     * Populates date fields
     * @param {Object} data - The intervention data
     */
    populateDates(data) {
        if (data.requestDate) {
            this.DOM.createdAt.textContent = formatDate(new Date(data.requestDate), CONFIG.SHOW_TIME_IN_DATES);
        } else {
            this.DOM.createdAt.textContent = "";
        }

        if (data.updatedAt) {
            this.DOM.updatedAt.textContent = formatDate(new Date(data.updatedAt), CONFIG.SHOW_TIME_IN_DATES);
        } else {
            this.DOM.updatedAt.textContent = "";
        }

        if (data.interventionDate) {
            this.DOM.interventionDate.value = formatDate(new Date(data.interventionDate), CONFIG.SHOW_TIME_IN_DATES);
        } else {
            this.DOM.interventionDate.value = "";
        }
    }

    /**
     * Populates user selection fields
     * @param {Object} data - The intervention data
     */
    populateUsers(data) {
        if (data.requesterUserId) {
            this.setValueInInputFromListOptionId(
                this.DOM.requesterUserInput,
                this.DOM.requesterUserIdInput,
                this.DOM.requesterUserList,
                data.requesterUserId
            );

            this.DOM.requesterUserLink.setAttribute("href", data.requesterUserId);
        } else {
            this.DOM.requesterUserInput.value = "";
            this.DOM.requesterUserIdInput.value = "";
            this.DOM.requesterUserLink.setAttribute("href", "#");
        }

        if (data.targetUserId) {
            this.setValueInInputFromListOptionId(
                this.DOM.interventionTargetUserInput,
                this.DOM.interventionTargetUserIdInput,
                this.DOM.interventionTargetUserList,
                data.targetUserId
            );

            this.DOM.interventionTargetUserLink.setAttribute("href", data.targetUserId);
        } else {
            this.DOM.interventionTargetUserInput.value = "";
            this.DOM.interventionTargetUserIdInput.value = "";
            this.DOM.interventionTargetUserLink.setAttribute("href", "#");
        }
    }

    /**
     * Populates material selection field
     * @param {Object} data - The intervention data
     */
    populateMaterial(data) {
        if (data.materialId) {
            this.setValueInInputFromListOptionId(
                this.DOM.materialInput,
                this.DOM.materialIdInput,
                this.DOM.materialList,
                data.materialId
            );

            this.DOM.materialLink.setAttribute("href", data.materialId);
        } else {
            this.DOM.materialInput.value = "";
            this.DOM.materialIdInput.value = "";
            this.DOM.materialLink.setAttribute("href", "#");
        }
    }

    /**
     * Populates intervention type and subtype fields
     * @param {Object} data - The intervention data
     */
    populateInterventionTypes(data) {
        if (data.typeId) {
            const option = this.DOM.interventionTypeSelect.querySelector(`[value="${data.typeId}"]`);
            if (option) {
                option.selected = true;
                this.updateSubtypeOptions();
            }
        } else {
            const option = this.DOM.interventionTypeSelect.querySelector(`[value=""]`);
            if (option) {
                option.selected = true;
                this.updateSubtypeOptions();
            }
        }

        if (data.subtypeId) {
            const option = this.DOM.interventionSubtypeSelect.querySelector(`[value="${data.subtypeId}"]`);
            if (option) {
                option.selected = true;
            }
        }
    }

    /**
     * Populates breadcrumb items (keywords and helpers)
     * @param {Object} data - The intervention data
     */
    populateBreadcrumbs(data) {
        if (data.keywords) {
            data.keywords.forEach(keyword => {
                this.addBreadcrumbItem(keyword.name, keyword.id, this.DOM.breadcrumbKeywords);
            });
        } else {
            this.DOM.breadcrumbKeywords.innerHTML = "";
        }

        if (data.helpers) {
            data.helpers.forEach(helper => {
                this.addBreadcrumbItem(helper.surname, helper.id, this.DOM.breadcrumbHelpers);
            });
        } else {
            this.DOM.breadcrumbHelpers.innerHTML = "";
        }
    }

    /**
     * Populates status radio buttons
     * @param {Object} data - The intervention data
     */
    populateStatus(data) {
        if (data.status) {
            this.DOM.statusRadios.forEach(status => {
                status.checked = (data.status == status.value);
            });
        } else {
            this.DOM.statusRadios.forEach(status => {
                status.checked = false
            });
        }
    }

    /**
     * Populates text input and textarea fields
     * @param {Object} data - The intervention data
     */
    populateTextFields(data) {
        if (data.description) {
            this.DOM.problemDescription.textContent = data.description;
            this.DOM.problemDescription.innerHTML = this.DOM.problemDescription.textContent.replace(/\r\n/g, "<br />");
        } else {
            this.DOM.problemDescription.textContent = "";
        }

        if (data.title) {
            this.DOM.title.value = data.title;
        } else {
            this.DOM.title.value = "";
        }

        if (data.comments) {
            this.DOM.comments.value = data.comments;
        } else {
            this.DOM.comments.value = "";
        }

        if (data.solution) {
            this.DOM.solution.value = data.solution;
        } else {
            this.DOM.solution.value = "";
        }
    }

    populateMessages(data) {
        this.DOM.messagesContainer.innerHTML = "";
        data.messages.forEach((message, index) => {
            const id = message.id;
            const isPublic = message.isPublic;
            const createdAt = message.createdAt ? new Date(message.createdAt) : null;

            const msgAuthorFirstname = message.author ? message.author.firstName : "";
            const msgAuthorLastName = message.author ? message.author.lastName : "";
            const msgAuthorUlgId = message.author ? message.author.ulgId : "";

            let values = [msgAuthorFirstname, msgAuthorLastName, msgAuthorUlgId].filter(v => v);
            let backgroundAvatarColor = "#4aa7ff"; // default

            if (values.length !== 0) {
                backgroundAvatarColor = ColorHelper.colorForValues(...values).hex;
            }

            // Avatar initials
            let initial = "?";
            if (msgAuthorFirstname || msgAuthorLastName) {
                initial = "";
                if (msgAuthorFirstname) initial += msgAuthorFirstname[0];
                if (msgAuthorLastName) initial += msgAuthorLastName[0];
            }

            // Date formatting
            let formattedDate = createdAt ? formatDate(createdAt, true) : "";

            // Escape and format message text
            let safeMsg = document.createElement("div");
            safeMsg.textContent = message.message || "";
            let msgWithBreaks = safeMsg.innerHTML.replace(/\r\n/g, "<br />");

            // Build container
            let messageContainer = document.createElement("div");
            messageContainer.classList.add("message_container");

            if (isPublic) {
                messageContainer.innerHTML = `
                <div class="message_header">
                     <div class="message_index_edit_button">
                        <span>#${index + 1}</span>
                        <button type="button" title="Éditer le message">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M535.6 85.7C513.7 63.8 478.3 63.8 456.4 85.7L432 110.1L529.9 208L554.3 183.6C576.2 161.7 576.2 126.3 554.3 104.4L535.6 85.7zM236.4 305.7C230.3 311.8 225.6 319.3 222.9 327.6L193.3 416.4C190.4 425 192.7 434.5 199.1 441C205.5 447.5 215 449.7 223.7 446.8L312.5 417.2C320.7 414.5 328.2 409.8 334.4 403.7L496 241.9L398.1 144L236.4 305.7zM160 128C107 128 64 171 64 224L64 480C64 533 107 576 160 576L416 576C469 576 512 533 512 480L512 384C512 366.3 497.7 352 480 352C462.3 352 448 366.3 448 384L448 480C448 497.7 433.7 512 416 512L160 512C142.3 512 128 497.7 128 480L128 224C128 206.3 142.3 192 160 192L256 192C273.7 192 288 177.7 288 160C288 142.3 273.7 128 256 128L160 128z"/></svg>
                        </button>
                     </div>
                     <div class="message_meta">
                        <div class="author_info">
                           <div class="author_avatar" style="background-color:${backgroundAvatarColor};">${initial}</div>
                           <div>
                              <div class="author_name"></div>
                              <div class="message_date"></div>
                           </div>
                        </div>
                        <div class="visibility_indicator visibility_public">
                           <svg class="visibility_icon" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                           </svg>
                           Publique
                        </div>
                     </div>
                  </div>
                  <div class="message_content">${msgWithBreaks}</div>
            `;
            } else {
                messageContainer.innerHTML = `
                <div class="message_header">
                     <div class="message_index_edit_button">
                        <span>#${index + 1}</span>
                        <button type="button" title="Éditer le message">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M535.6 85.7C513.7 63.8 478.3 63.8 456.4 85.7L432 110.1L529.9 208L554.3 183.6C576.2 161.7 576.2 126.3 554.3 104.4L535.6 85.7zM236.4 305.7C230.3 311.8 225.6 319.3 222.9 327.6L193.3 416.4C190.4 425 192.7 434.5 199.1 441C205.5 447.5 215 449.7 223.7 446.8L312.5 417.2C320.7 414.5 328.2 409.8 334.4 403.7L496 241.9L398.1 144L236.4 305.7zM160 128C107 128 64 171 64 224L64 480C64 533 107 576 160 576L416 576C469 576 512 533 512 480L512 384C512 366.3 497.7 352 480 352C462.3 352 448 366.3 448 384L448 480C448 497.7 433.7 512 416 512L160 512C142.3 512 128 497.7 128 480L128 224C128 206.3 142.3 192 160 192L256 192C273.7 192 288 177.7 288 160C288 142.3 273.7 128 256 128L160 128z"/></svg>
                        </button>
                     </div>
                     <div class="message_meta">
                        <div class="author_info">
                           <div class="author_avatar" style="background-color:${backgroundAvatarColor};">${initial}</div>
                           <div>
                              <div class="author_name"></div>
                              <div class="message_date"></div>
                           </div>
                        </div>
                        <div class="visibility_indicator visibility_private">
                           <svg class="visibility_icon" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                           </svg>
                           Visible pour l'UDI <br>uniquement.
                        </div>
                     </div>
                  </div>
                  <div class="message_content">${msgWithBreaks}</div>
            `;
            }

            // Escape text fields safely via textContent
            messageContainer.querySelector(".author_name").textContent = `${msgAuthorFirstname} ${msgAuthorLastName}`;
            messageContainer.querySelector(".message_date").textContent = formattedDate;

            this.DOM.messagesContainer.appendChild(messageContainer);
        });
    }



    addBreadcrumbItem(text, value, container) {
        const item = document.createElement('div');
        item.className = CONFIG.BREADCRUMB_ITEM_CLASS;

        // Determine the input name based on container
        const inputName = container === this.DOM.breadcrumbKeywords ? 'keyword_ids[]' : 'helper_ids[]';

        item.innerHTML = `
      ${text}
        <input type="hidden" name="${inputName}" value="${value}">
        <span class="remove" role="button" onclick="this.parentElement.remove()">&times;</span>
        `;

        container.appendChild(item);
    }

    /**
     * Sets the value of an input based on a data list option ID
     * @param {HTMLInputElement} input - The input element to set
     * @param {HTMLInputElement} hiddenInput - The input element to set
     * @param {HTMLDataListElement} list - The data list to search
     * @param {string|number} id - The ID to match against data-value-id attribute
     */
    setValueInInputFromListOptionId(input, hiddenInput, list, id) {
        const option = list.querySelector(`[data-value-id="${id}"]`);
        if (option) {
            input.value = option.value;
            hiddenInput.value = id;
        } else {
            console.warn(`Option with data-value-id="${id}" not found in list`);
        }
    }

    updateSubtypeOptions() {
        const interventionTypeId = this.DOM.interventionTypeSelect.value;

        this.DOM.interventionSubtypeOptions.forEach(option => {
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

    /**
     * Handles keyword select change events
     */
    handleKeywordSelectChange() {
        if (this.DOM.keywordSelect.value === "") return;

        const keywordId = this.DOM.keywordSelect.value;
        const keywordText = this.DOM.keywordSelect.options[this.DOM.keywordSelect.selectedIndex].text;

        this.addBreadcrumbItem(keywordText, keywordId, this.DOM.breadcrumbKeywords);
        this.DOM.keywordSelect.value = ""; // Reset selection
    }


    handleHelperSelectChange() {
        if (this.DOM.helpersSelect.value === "") return;

        const helperId = this.DOM.helpersSelect.value;
        const helperText = this.DOM.helpersSelect.options[this.DOM.helpersSelect.selectedIndex].text;

        this.addBreadcrumbItem(helperText, helperId, this.DOM.breadcrumbHelpers);
        this.DOM.helpersSelect.value = ""; // Reset selection
    }


    registerEvents() {
        // Intervention type change handler
        this.DOM.interventionTypeSelect.addEventListener("change", () => {
            this.updateSubtypeOptions();
        });

        // Breadcrumb select handlers
        this.DOM.keywordSelect.addEventListener("change", () => {
            this.handleKeywordSelectChange();
        });

        this.DOM.helpersSelect.addEventListener("change", () => {
            this.handleHelperSelectChange();
        });

        // Autocomplete for requester user
        this.DOM.requesterUserInput.addEventListener("input", (e) => {
            const inputValue = convertToAscii(e.target.value.trim().toUpperCase());
            const registeredOption = this.requesterUserMap.get(inputValue);
            if (registeredOption) {
                this.DOM.requesterUserInput.value = registeredOption.getAttribute("value");
                this.DOM.requesterUserIdInput.value = registeredOption.getAttribute("data-value-id");
                this.DOM.requesterUserLink.setAttribute("href", this.DOM.requesterUserIdInput.value);
            } else {
                this.DOM.requesterUserIdInput.value = "";
            }
        });

        // Autocomplete for intervention target user
        this.DOM.interventionTargetUserInput.addEventListener("input", (e) => {
            const inputValue = convertToAscii(e.target.value.trim().toUpperCase());
            const registeredOption = this.interventionUserTargetMap.get(inputValue);
            if (registeredOption) {
                this.DOM.interventionTargetUserInput.value = registeredOption.getAttribute("value");
                this.DOM.interventionTargetUserIdInput.value = registeredOption.getAttribute("data-value-id");
                this.DOM.interventionTargetUserLink.setAttribute("href", this.DOM.interventionTargetUserIdInput.value);
            } else {
                this.DOM.interventionTargetUserIdInput.value = "";
            }
        });

        // Autocomplete for material input
        this.DOM.materialInput.addEventListener("input", (e) => {
            const inputValue = convertToAscii(e.target.value.trim().toUpperCase());
            const registeredOption = this.materialMap.get(inputValue);
            if (registeredOption) {
                this.DOM.materialInput.value = registeredOption.getAttribute("value");
                this.DOM.materialIdInput.value = registeredOption.getAttribute("data-value-id");
                this.DOM.materialLink.setAttribute("href", this.DOM.materialIdInput.value);
            } else {
                this.DOM.materialIdInput.value = "";
            }
        });
    }
}