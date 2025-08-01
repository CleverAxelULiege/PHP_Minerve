import { formatDate } from "../helpers/date.js";
import { InterventionApiCall } from "./api/InterventionApiCall.js";
//<img.*src=["'](.*)['"].*\/?>

const REQUESTER_USER_LIST = document.getElementById("requester_user_list");
const REQUESTER_USE_INPUT = document.getElementById("requester_user");

const INTERVENTION_TARGET_USER_INPUT = document.getElementById("intervention_target_user");
const INTERVENTION_TARGET_USER_LIST = document.getElementById("intervention_target_user_list");

const MATERIAL_INPUT = document.getElementById("material");
const MATERIAL_LIST = document.getElementById("material_list");

const UPDATED_AT = document.getElementById("updated_at");
const CREATED_AT = document.getElementById("created_at");

const REQUEST_IP = document.getElementById("request_ip");
const INTERVENTION_DATE = document.getElementById("intervention_date");
const AGENDA_DATE = document.getElementById("agenda_date");
const AGENDA_COMMENTS = document.getElementById("agenda_comments");
const STATUS = document.querySelectorAll("[name='status']");
const PROBLEM_DESCRIPTION = document.getElementById("problem");
const TITLE = document.getElementById("title");
const COMMENTS = document.getElementById("comments");
const SOLUTION = document.getElementById("solution");


// REQUESTER_USE_INPUT.value = REQUESTER_USER_LIST.querySelector(`[data-value-id="${20}"]`).value;

// setValueInInputFromListOptionId(REQUESTER_USE_INPUT, REQUESTER_USER_LIST, 20);

//#region SIDE WINDOW
const INTERVENTIONS_ROWS = document.querySelectorAll(".intervention_row");
/**@type {HTMLElement} */
const INTERVENTION_DETAILS_CONTAINER = document.querySelector(".intervention_details_container");
/**@type {HTMLElement} */
const INTERVENTION_DETAILS_CONTENT = INTERVENTION_DETAILS_CONTAINER.querySelector(".content");
//#endregion

/**@type {HTMLOptionElement[]} */
const INTERVENTION_SUBTYPE_OPTIONS = document.querySelectorAll("#intervention_subtype option");

/**@type {HTMLInputElement} */
const INTERVENTION_TYPE_SELECT = document.querySelector("#intervention_type");

/**@type {HTMLInputElement} */
const INTERVENTION_SUBTYPE_SELECT = document.getElementById("intervention_subtype");

/**@type {HTMLElement} */
const BREADCRUMB_KEYWORDS = document.querySelector("#breadcrumb_keywords");
/**@type {HTMLSelectElement} */
const KEYWORD_SELECT = document.querySelector("#keywords");

/**@type {HTMLElement} */
const BREADCRUMB_HELPERS = document.querySelector("#breadcrumb_helpers");
/**@type {HTMLSelectElement} */
const HELPERS_SELECT = document.querySelector("#helpers");

let getDetailsIntervention = {
    requestSent: false,
    responseReceived: false,
    abortController: new AbortController(),
};


// InterventionApiCall.getInterventionById(11100, getDetailsIntervention.abortController.signal).then((data) => {
//     getDetailsIntervention.requestSent = false;
//     getDetailsIntervention.responseReceived = true;
//     const showTime = true;

//     if (data.requestDate) {
//         CREATED_AT.textContent = formatDate(new Date(data.requestDate), showTime);
//     }

//     if (data.updatedAt) {
//         UPDATED_AT.textContent = formatDate(new Date(data.updatedAt), showTime);
//     }

//     if (data.requesterUserId) {
//         setValueInInputFromListOptionId(REQUESTER_USE_INPUT, REQUESTER_USER_LIST, data.requesterUserId);
//     }
//     if (data.targetUserId) {
//         setValueInInputFromListOptionId(INTERVENTION_TARGET_USER_INPUT, INTERVENTION_TARGET_USER_LIST, data.targetUserId);
//     }

//     if (data.materialId) {
//         setValueInInputFromListOptionId(MATERIAL_INPUT, MATERIAL_LIST, data.materialId);
//     }

//     if (data.typeId) {
//         const option = INTERVENTION_TYPE_SELECT.querySelector(`[value="${data.typeId}"]`);
//         if (option) {
//             option.selected = true;
//             onChangeInterventionType();
//         }
//     }

//     if (data.subtypeId) {
//         const option = INTERVENTION_SUBTYPE_SELECT.querySelector(`[value="${data.subtypeId}"]`);
//         console.log(option);

//         if (option) {
//             option.selected = true;
//         }
//     }

//     if (data.keywords) {
//         data.keywords.forEach((keyword) => {
//             addBreadcrumbItem(keyword.name, keyword.id, BREADCRUMB_KEYWORDS);
//         });
//     }

//     if (data.interventionDate) {
//         INTERVENTION_DATE.textContent = formatDate(new Date(data.interventionDate), showTime);
//     }

//     //TO DO AGENDA_DATE
//     //TO DO AGENDA COMMENTS

//     if (data.helpers) {
//         data.helpers.forEach((helper) => {
//             addBreadcrumbItem(helper.surname, helper.id, BREADCRUMB_HELPERS);
//         });
//     }

//     if (data.status) {
//         STATUS.forEach(status => {
//             if (data.status == status.value) {
//                 status.checked = true;
//             }
//         });;
//     }

//     if (data.description) {
//         PROBLEM_DESCRIPTION.textContent = data.description;
//     }

//     if (data.title) {
//         TITLE.value = data.title;
//     }

//     if (data.comments) {
//         COMMENTS.value = data.comments;
//     }

//     if (data.solution) {
//         SOLUTION.value = data.solution;
//     }



//     // console.log(data.requestDate);
//     // console.log(formatDate(new Date(data.requestDate), true));
//     // // console.log(new Date(data.requestDate));

// });

INTERVENTIONS_ROWS.forEach((row) => {
    row.addEventListener("click", () => {
        onClickInterventionRow(row);
    })
});

/**
 * @param {HTMLElement} row 
*/
function onClickInterventionRow(row) {
    INTERVENTIONS_ROWS.forEach((_row) => {
        _row.classList.remove("active");
    });
    row.classList.add("active");

    const interventionId = row.getAttribute("data-intervention-id");
    if (!interventionId) {
        throw new Error("InterventionId is NULL");
    }

    if (getDetailsIntervention.requestSent && !getDetailsIntervention.responseReceived) {
        getDetailsIntervention.abortController.abort();
    }

    getDetailsIntervention.requestSent = true;
    getDetailsIntervention.responseReceived = false;
    getDetailsIntervention.abortController = new AbortController();

    InterventionApiCall.getInterventionById(interventionId, getDetailsIntervention.abortController.signal).then((data) => {
        if (data == null) {
            console.warn("Data is null. Possible problem : the id isn't an integer, a problem occured server side.");
        }

        populateForm(data);

    });

    INTERVENTION_DETAILS_CONTAINER.classList.remove("hidden");
    INTERVENTION_DETAILS_CONTAINER.ontransitionend = () => {
        if (INTERVENTION_DETAILS_CONTAINER.classList.contains("hidden"))
            return;

        INTERVENTION_DETAILS_CONTENT.classList.remove("hidden");
    }
}

INTERVENTION_TYPE_SELECT.addEventListener("change", () => {
    onChangeInterventionType();
});
onChangeInterventionType();

KEYWORD_SELECT.addEventListener("change", () => { onChangeKeywordSelect() });
HELPERS_SELECT.addEventListener("change", () => onChangeHelperSelect());

function onChangeKeywordSelect() {
    if (KEYWORD_SELECT.value == "") {
        return;
    }

    const keywordId = KEYWORD_SELECT.value;
    const keywordText = KEYWORD_SELECT.options[KEYWORD_SELECT.selectedIndex].text;
    addBreadcrumbItem(keywordText, keywordId, BREADCRUMB_KEYWORDS);
}

function onChangeHelperSelect() {
    if (HELPERS_SELECT.value == "") {
        return;
    }

    const helperId = HELPERS_SELECT.value;
    const helperText = HELPERS_SELECT.options[HELPERS_SELECT.selectedIndex].text;
    addBreadcrumbItem(helperText, helperId, BREADCRUMB_HELPERS);
}

/**
 * 
 * @param {string} text 
 * @param {string} value 
 * @param {HTMLElement} container 
 */
function addBreadcrumbItem(text, value, container) {
    const item = document.createElement('div');
    item.className = 'breadcrumb_item';

    item.innerHTML = `
                ${text}
                <input type="hidden" name="keyword_ids[]" value="${value}">
                <span class="remove" onclick="removeBreadcrumbItem(this)">&times;</span>
            `;
    container.appendChild(item);
}

/**
 * @param {HTMLElement} element 
 */
function removeBreadcrumbItem(element) {
    element.parentElement.remove();
}

function onChangeInterventionType() {
    const interventionTypeId = INTERVENTION_TYPE_SELECT.value;
    INTERVENTION_SUBTYPE_OPTIONS.forEach(option => {
        if (interventionTypeId == "") {
            option.style.display = "none";
        }
        else if (option.getAttribute("data-intervention-type-id") == interventionTypeId) {
            option.style.display = "";
        } else {
            option.style.display = "none";
        }

        if (option.value == "") {
            option.selected = true;
        }
    });
}

/**
 * 
 * @param {HTMLInputElement} input 
 * @param {HTMLDataListElement} list 
 */
function setValueInInputFromListOptionId(input, list, id) {
    const option = list.querySelector(`[data-value-id="${id}"]`);
    if (option) {
        input.value = option.value;
    } else {
        console.warn(`Option with the data-value-id of : ${id}, not found`);
    }
}

function populateForm(data) {
    BREADCRUMB_HELPERS.innerHTML = "";
    BREADCRUMB_KEYWORDS.innerHTML = "";

    getDetailsIntervention.requestSent = false;
    getDetailsIntervention.responseReceived = true;

    getDetailsIntervention.requestSent = false;
    getDetailsIntervention.responseReceived = true;
    const showTime = true;

    if (data.requestDate) {
        CREATED_AT.textContent = formatDate(new Date(data.requestDate), showTime);
    }

    if (data.updatedAt) {
        UPDATED_AT.textContent = formatDate(new Date(data.updatedAt), showTime);
    }

    if (data.requesterUserId) {
        setValueInInputFromListOptionId(REQUESTER_USE_INPUT, REQUESTER_USER_LIST, data.requesterUserId);
    }
    if (data.targetUserId) {
        setValueInInputFromListOptionId(INTERVENTION_TARGET_USER_INPUT, INTERVENTION_TARGET_USER_LIST, data.targetUserId);
    }

    if (data.materialId) {
        setValueInInputFromListOptionId(MATERIAL_INPUT, MATERIAL_LIST, data.materialId);
    }

    if (data.typeId) {
        const option = INTERVENTION_TYPE_SELECT.querySelector(`[value="${data.typeId}"]`);
        if (option) {
            option.selected = true;
            onChangeInterventionType();
        }
    }

    if (data.subtypeId) {
        const option = INTERVENTION_SUBTYPE_SELECT.querySelector(`[value="${data.subtypeId}"]`);
        console.log(option);

        if (option) {
            option.selected = true;
        }
    }

    if (data.keywords) {
        data.keywords.forEach((keyword) => {
            addBreadcrumbItem(keyword.name, keyword.id, BREADCRUMB_KEYWORDS);
        });
    }

    if (data.interventionDate) {
        INTERVENTION_DATE.textContent = formatDate(new Date(data.interventionDate), showTime);
    }

    //TO DO AGENDA_DATE
    //TO DO AGENDA COMMENTS

    if (data.helpers) {
        data.helpers.forEach((helper) => {
            addBreadcrumbItem(helper.surname, helper.id, BREADCRUMB_HELPERS);
        });
    }

    if (data.status) {
        STATUS.forEach(status => {
            if (data.status == status.value) {
                status.checked = true;
            }
        });;
    }

    if (data.description) {
        PROBLEM_DESCRIPTION.textContent = data.description;
    }

    if (data.title) {
        TITLE.value = data.title;
    }

    if (data.comments) {
        COMMENTS.value = data.comments;
    }

    if (data.solution) {
        SOLUTION.value = data.solution;
    }
}