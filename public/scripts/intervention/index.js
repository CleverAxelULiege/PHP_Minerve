import { InterventionFormManager } from "./InterventionFormManager.js";

const DOM = {
    // Side panel elements
    interventionRows: document.querySelectorAll(".intervention_row"),
    interventionDetailsContainer: document.querySelector(".intervention_details_container"),
    interventionDetailsContent: document.querySelector(".intervention_details_container .content"),
    serviceColumns : document.querySelectorAll(".service_column"),
    categoryColumns : document.querySelectorAll(".category_column"),
};

const interventionFormManager = new InterventionFormManager(document.querySelector(".intervention_details_container .content"));

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

        interventionFormManager.loadInterventionDetails(interventionId);
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
     * Shows the intervention details panel with smooth transition
     */
    static showDetailsPanel() {
        DOM.interventionDetailsContainer.classList.remove("hidden");
        DOM.interventionDetailsContainer.ontransitionend = () => {
            DOM.interventionDetailsContainer.ontransitionend = null;
            if (!DOM.interventionDetailsContainer.classList.contains("hidden")) {
                DOM.interventionDetailsContent.classList.remove("hidden");
                DOM.serviceColumns.forEach((col) => {col.style.display = "none"});
                DOM.categoryColumns.forEach((col) => {col.style.display = "none"});
            }
        };
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

    }
}




class App {
    static init() {
        EventListeners.registerAll();
    }
}

App.init();




