const INTERVENTIONS_ROWS = document.querySelectorAll(".intervention_row");

/**@type {HTMLElement} */
const INTERVENTION_DETAILS_CONTAINER = document.querySelector(".intervention_details_container");

/**@type {HTMLElement} */
const INTERVENTION_DETAILS_CONTENT = INTERVENTION_DETAILS_CONTAINER.querySelector(".content");

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
    if(!interventionId){
        throw new Error("InterventionId is NULL");
    }

    INTERVENTION_DETAILS_CONTAINER.classList.remove("hidden");
    INTERVENTION_DETAILS_CONTAINER.ontransitionend = () => {
        if(INTERVENTION_DETAILS_CONTAINER.classList.contains("hidden"))
            return;

        console.log("hello");
        
        INTERVENTION_DETAILS_CONTENT.classList.remove("hidden");
    }
}