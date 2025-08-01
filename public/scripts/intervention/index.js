const INTERVENTIONS_ROWS = document.querySelectorAll(".intervention_row");

/**@type {HTMLElement} */
const INTERVENTION_DETAILS_CONTAINER = document.querySelector(".intervention_details_container");

/**@type {HTMLElement} */
const INTERVENTION_DETAILS_CONTENT = INTERVENTION_DETAILS_CONTAINER.querySelector(".content");

/**@type {HTMLOptionElement[]} */
const INTERVENTION_SUBTYPE_OPTIONS = document.querySelectorAll("#intervention_subtype option");
/**@type {HTMLInputElement} */
const INTERVENTION_TYPE_SELECT = document.querySelector("#intervention_type");

/**@type {HTMLElement} */
const BREADCRUMB_KEYWORDS = document.querySelector("#breadcrumb_keywords");
/**@type {HTMLSelectElement} */
const KEYWORD_SELECT = document.querySelector("#keywords");

/**@type {HTMLElement} */
const BREADCRUMB_HELPERS = document.querySelector("#breadcrumb_helpers");
/**@type {HTMLSelectElement} */
const HELPERS_SELECT = document.querySelector("#helpers");



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