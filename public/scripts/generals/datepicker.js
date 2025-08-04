class DatePicker {
    /**
     * @param {HTMLElement} datePickerElement 
     */
    constructor(datePickerElement) {
        this.rootElement = datePickerElement;
        this.addTimeSelection = this.rootElement.getAttribute("data-add-time") == "true";
        this.addDefaultDateIfEmpty = this.rootElement.getAttribute("data-default-date-if-empty") == "true";
        this.minuteSelectStep = 5;
        this.buildBaseDatePicker();


        /**@type {HTMLElement} */
        this.monthYearDisplay = this.rootElement.querySelector("#month_year_display");
        /**@type {HTMLElement} */
        this.calendar = this.rootElement.querySelector("#calendar");
        /**@type {HTMLInputElement} */
        this.input = this.rootElement.querySelector("input");
        /**@type {HTMLInputElement} */
        this.yearSelect = this.rootElement.querySelector("#year_select");
        /**@type {HTMLInputElement} */
        this.monthSelect = this.rootElement.querySelector("#month_select");

        /**@type {HTMLInputElement} */
        this.hourSelect = this.rootElement.querySelector("#hour_select");
        /**@type {HTMLInputElement} */
        this.minuteSelect = this.rootElement.querySelector("#minute_select");



        /**@type {HTMLButtonElement} */
        this.prevMonthButton = this.rootElement.querySelector("#prev_month_button");
        /**@type {HTMLButtonElement} */
        this.nextMonthButton = this.rootElement.querySelector("#next_month_button");
        /**@type {HTMLButtonElement} */
        this.toggleCalendarButton = this.rootElement.querySelector("#calendar_toggle_button");


        /**@type {HTMLDivElement} */
        this.daysGridContainer = this.rootElement.querySelector("#days_grid_container");

        this.selectedDate = new Date();
        this.currentMonthDisplay = new Date();
        this.today = new Date();


        // day(1-2 digits), non-digit, month(1-2), non-digit, year(4), with optional surrounding whitespace, the delimiter can be whatever except a number
        // don't care about the spaces before or after.
        this.regexDateParser = this.addTimeSelection ? /^\s*(\d{1,2})\s*\D\s*(\d{1,2})\s*\D\s*(\d{4})\D+(\d{1,2})\s*\D\s*(\d{1,2})\s*$/ : /^\s*(\d{1,2})\s*\D\s*(\d{1,2})\s*\D\s*(\d{4})\s*$/;


        this.monthNames = [
            "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
            "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
        ];

        this.initEventListeners();
        this.buildOptionsInSelect();
        this.initSelectedDateFromInput();
        this.selectOptionsFromMonthInDisplay();
    }

    buildBaseDatePicker() {
        this.rootElement.innerHTML +=
            `
        <button class="calendar_toggle_button" type="button" id="calendar_toggle_button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M128 0c17.7 0 32 14.3 32 32l0 32 128 0 0-32c0-17.7 14.3-32 32-32s32 14.3 32 32l0 32 32 0c35.3 0 64 28.7 64 64l0 288c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 128C0 92.7 28.7 64 64 64l32 0 0-32c0-17.7 14.3-32 32-32zM64 240l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm128 0l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zM64 368l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zm112 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16z" />
            </svg>
        </button>
        <div class="calendar " id="calendar">
            <div class="calendar_header">
                <button class="nav_button" type="button" id="prev_month_button">‹</button>
                <div class="month_year" id="month_year_display">
                    <select id="month_select">
                    </select>
                    <select id="year_select">

                    </select>
                </div>
                <button class="nav_button" type="button" id="next_month_button">›</button>
            </div>

            <div class="days_header">
                <div class="day_name">Lun</div>
                <div class="day_name">Mar</div>
                <div class="day_name">Mer</div>
                <div class="day_name">Jeu</div>
                <div class="day_name">Ven</div>
                <div class="day_name">Sam</div>
                <div class="day_name">Dim</div>
            </div>

            <div class="days_grid" id="days_grid_container"></div>
            ${this.addTimeSelection
                ?
                `<div class="hour_minute" id="hour_minute_display">
                    <span class="hour_label">Heure :</span>
                    <select id="hour_select"></select>
                    <span>:</span>
                    <select id="minute_select"></select>
                </div> ` : ``
            }
        </div>
        `;
    }

    initSelectedDateFromInput() {
        const value = this.input.value;
        const matches = value.match(this.regexDateParser);

        if (matches && this.isDateValid(matches)) {
            const day = parseInt(matches[1], 10);
            const month = parseInt(matches[2], 10) - 1;
            const year = parseInt(matches[3], 10);
            const displaySelectedDate = true;
            this.setSelectedDate(year, month, day, displaySelectedDate);

            if (this.addTimeSelection) {
                const hour = parseInt(matches[4]) % 24;
                const minute = parseInt(matches[5]) % 60;
                this.reformatDatetimeInInput({ day: this.selectedDate.getDate(), month: this.selectedDate.getMonth() + 1, year: this.selectedDate.getFullYear(), hour: hour, minute: minute })
                this.updateTimeSelect(hour, minute);
            } else {
                this.reformatDateInInput({ day: this.selectedDate.getDate(), month: this.selectedDate.getMonth() + 1, year: this.selectedDate.getFullYear() });
            }
        } else {
            if (value.trim() != "") { //invalid date, don't care about the time
                this.input.classList.add("invalid");
            } else {
                if (this.addDefaultDateIfEmpty && value.trim() == "") { //if input blank, add a default date
                    if (this.addTimeSelection) {
                        this.reformatDatetimeInInput({ day: this.currentMonthDisplay.getDate(), month: this.currentMonthDisplay.getMonth() + 1, year: this.currentMonthDisplay.getFullYear(), hour: this.currentMonthDisplay.getHours(), minute: this.currentMonthDisplay.getMinutes() });
                    } else {
                        this.reformatDateInInput({ day: this.currentMonthDisplay.getDate(), month: this.currentMonthDisplay.getMonth() + 1, year: this.currentMonthDisplay.getFullYear() });
                    }
                }
            }
            this.render();
        }
    }

    selectOptionsFromMonthInDisplay() {
        try {
            this.yearSelect.querySelector(`option[value="${this.currentMonthDisplay.getFullYear()}"]`).selected = true;
            this.monthSelect.querySelector(`option[value="${this.currentMonthDisplay.getMonth()}"]`).selected = true;
        } catch {
            console.warn("Year or month option out of range of the select for the month being displayed.");
        }
    }

    selectOptionsFromSelectedDate() {
        try {
            this.yearSelect.querySelector(`option[value="${this.selectedDate.getFullYear()}"]`).selected = true;
            this.monthSelect.querySelector(`option[value="${this.selectedDate.getMonth()}"]`).selected = true;
        } catch {
            console.warn("Year or month option out of range of the select for the selected date.");
        }
    }

    buildOptionsInSelect() {
        for (let i = 0; i < this.monthNames.length; i++) {
            const option = document.createElement("option");
            option.value = i;
            option.innerHTML = this.monthNames[i];
            this.monthSelect.appendChild(option);
        }

        let minYear = parseInt(this.rootElement.getAttribute("data-min-year"));
        let maxYear = parseInt(this.rootElement.getAttribute("data-max-year"));
        minYear = isNaN(minYear) ? 1900 : minYear;
        maxYear = isNaN(maxYear) ? this.today.getFullYear() + 10 : maxYear;

        for (let i = minYear; i <= maxYear; i++) {
            const option = document.createElement("option");
            option.value = i;
            option.innerHTML = i;
            this.yearSelect.appendChild(option);
        }

        if (!this.addTimeSelection)
            return;

        const date = new Date();
        const currentHour = date.getHours();
        const currentMinute = date.getMinutes();

        const stepHour = 1;
        for (let hour = 0; hour <= 23; hour += stepHour) {
            const formatHour = String(hour).padStart(2, '0');
            const option = document.createElement("option");

            if (hour == currentHour)
                option.selected = true;

            option.value = formatHour;
            option.innerHTML = formatHour;
            this.hourSelect.appendChild(option);
        }

        const stepMinute = this.minuteSelectStep;
        for (let minute = 0; minute < 60; minute += stepMinute) {
            const formatMinute = String(minute).padStart(2, '0');
            const option = document.createElement("option");
            option.value = formatMinute;
            option.innerHTML = formatMinute;
            this.minuteSelect.appendChild(option);
        }

        this.selectClosestOptionFromMinute(currentMinute);
    }

    /**
     * 
     * @param {number} hour 
     * @param {number} minute 
     */
    updateTimeSelect(hour, minute) {
        hour = hour % 24;
        minute = minute % 60;
        const hourPadded = String(hour).padStart(2, '0');
        this.hourSelect.querySelector(`option[value="${hourPadded}"]`).selected = true;
        this.selectClosestOptionFromMinute(minute);
    }

    /**
     * @param {number} minute 
     */
    selectClosestOptionFromMinute(minute) {
        let minuteValueOption = "00";
        const stepMinute = this.minuteSelectStep;
        for (let minuteIterator = 0; minuteIterator < 60; minuteIterator += stepMinute) {
            const diffCurrentMinute = Math.abs(minuteIterator - minute);
            const diffNextMinute = Math.abs((minuteIterator + stepMinute) - minute);
            if (diffCurrentMinute >= 0 && diffCurrentMinute <= stepMinute && diffNextMinute >= 0 && diffNextMinute <= stepMinute) {
                if (diffCurrentMinute <= diffNextMinute) {
                    minuteValueOption = String(minuteIterator).padStart(2, '0');
                    break;
                } else {
                    if (minuteIterator + stepMinute >= 60) {
                        minuteValueOption = String(minuteIterator).padStart(2, '0');
                        break;
                    } else {
                        minuteValueOption = String(minuteIterator + stepMinute).padStart(2, '0');
                        break;
                    }
                }
            }
        }

        this.minuteSelect.querySelector(`option[value="${minuteValueOption}"]`).selected = true;
    }

    initEventListeners() {
        this.prevMonthButton.addEventListener("click", () => this.navigateToPreviousMonth());
        this.nextMonthButton.addEventListener("click", () => this.navigateToNextMonth());
        this.toggleCalendarButton.addEventListener("click", () => this.toggleCalendarVisibility());
        document.addEventListener("click", (e) => this.handleOutsideClick(e));
        this.input.addEventListener("input", () => this.handleInputChange());
        this.input.addEventListener("blur", (e) => this.handleInputBlur(e));
        this.yearSelect.addEventListener("change", () => this.handleYearChange());
        this.monthSelect.addEventListener("change", () => this.handleMonthChange());
        this.daysGridContainer.addEventListener("click", (e) => this.handleDayClick(e));

        if (this.addTimeSelection) {
            this.hourSelect.addEventListener("change", () => this.handleTimeChange());
            this.minuteSelect.addEventListener("change", () => this.handleTimeChange());
        }
    }

    navigateToPreviousMonth() {
        this.currentMonthDisplay = new Date(this.currentMonthDisplay.getFullYear(), this.currentMonthDisplay.getMonth() - 1, 1);
        this.selectOptionsFromMonthInDisplay();
        this.render();
    }

    navigateToNextMonth() {
        this.currentMonthDisplay = new Date(this.currentMonthDisplay.getFullYear(), this.currentMonthDisplay.getMonth() + 1, 1);
        this.selectOptionsFromMonthInDisplay();
        this.render();
    }

    toggleCalendarVisibility() {
        const isVisible = this.calendar.classList.toggle("visible");
        this.calendar.setAttribute("aria-hidden", !isVisible);
    }

    handleOutsideClick(e) {
        if (this.calendar.classList.contains("visible") && !this.rootElement.contains(e.target)) {
            this.calendar.classList.remove("visible");
            this.calendar.setAttribute("aria-hidden", "true");
        }
    }

    handleInputChange() {
        const value = this.input.value;
        const matches = value.match(this.regexDateParser);

        if (matches && this.isDateValid(matches)) {
            // this.reformatDateInInput({
            //     day: parseInt(matches[1]),
            //     month: parseInt(matches[2]),
            //     year: parseInt(matches[3])
            // });
            this.input.classList.remove("invalid");

            // Parse the input string into numbers
            if (this.addTimeSelection) {
                let [, day, month, year, hour, minute] = matches.map(Number);
                this.setSelectedDate(year, month - 1, day, true);
                this.updateTimeSelect(hour, minute);
            } else {
                let [, day, month, year] = matches.map(Number);
                this.setSelectedDate(year, month - 1, day, true);
            }
        }
    }

    handleInputBlur(e) {
        const value = this.input.value.trim();

        if(value == "" && !this.addDefaultDateIfEmpty){
            this.input.classList.remove("invalid");
            return;
        }

        const matches = value.match(this.regexDateParser);
        if (!matches || !this.isDateValid(matches)) {
            this.input.classList.add("invalid");
            return;
        }

        if (this.addTimeSelection) {
            const hour = parseInt(matches[4]) % 24;
            const minute = parseInt(matches[5]) % 60;
            this.reformatDatetimeInInput({ day: this.selectedDate.getDate(), month: this.selectedDate.getMonth() + 1, year: this.selectedDate.getFullYear(), hour: hour, minute: minute })
            this.updateTimeSelect(hour, minute);
        } else {
            this.reformatDateInInput({ day: this.selectedDate.getDate(), month: this.selectedDate.getMonth() + 1, year: this.selectedDate.getFullYear() });
        }
        this.input.classList.remove("invalid");
    }

    handleYearChange() {
        const value = this.yearSelect.value;
        this.currentMonthDisplay = new Date(value, this.currentMonthDisplay.getMonth(), 1);
        this.render();
    }

    handleMonthChange() {
        const value = this.monthSelect.value;
        this.currentMonthDisplay = new Date(this.currentMonthDisplay.getFullYear(), value, 1);
        this.render();
    }

    handleTimeChange() {
        const hour = this.hourSelect.value;
        const minute = this.minuteSelect.value;
        this.reformatDatetimeInInput({ day: this.selectedDate.getDate(), month: this.selectedDate.getMonth() + 1, year: this.selectedDate.getFullYear(), hour: hour, minute: minute })
    }

    handleDayClick(e) {
        // Look on what kind of cells the user is clicking, if the cell is not "empty", 
        // we retrieve its data-attribute, the day, and we set the selected date.
        if (e.target.classList.contains("day_cell") && !e.target.classList.contains("empty")) {
            const day = parseInt(e.target.getAttribute("data-day"));
            this.setSelectedDate(this.currentMonthDisplay.getFullYear(), this.currentMonthDisplay.getMonth(), day);

            if (this.addTimeSelection) {
                const hour = this.hourSelect.value;
                const minute = this.minuteSelect.value;
                this.reformatDatetimeInInput({ day: day, month: this.currentMonthDisplay.getMonth() + 1, year: this.currentMonthDisplay.getFullYear(), hour: hour, minute: minute })
            } else {
                this.reformatDateInInput({
                    day: day,
                    month: this.currentMonthDisplay.getMonth() + 1,
                    year: this.currentMonthDisplay.getFullYear()
                });
            }

            setTimeout(() => {
                this.input.classList.remove("invalid");
            }, 0);
        }
    }

    /**
     * @param {{ day: number, month: number, year: number }} dateObj
     */
    reformatDateInInput({ day, month, year }) {
        const paddedDay = String(day).padStart(2, '0');
        const paddedMonth = String(month).padStart(2, '0');

        this.input.value = `${paddedDay}/${paddedMonth}/${year}`;
    }
    /**
     * @param {{ day: number, month: number, year: number, hour:number, minute:number }} dateObj
     */
    reformatDatetimeInInput({ day, month, year, hour, minute }) {
        const paddedDay = String(day).padStart(2, '0');
        const paddedMonth = String(month).padStart(2, '0');
        const paddedHour = String(hour).padStart(2, '0');
        const paddedMinute = String(minute).padStart(2, '0');

        this.input.value = `${paddedDay}/${paddedMonth}/${year} ${paddedHour}:${paddedMinute}`;
    }

    isDateValid(matches) {
        //parse the input string into number
        let [, day, month, year] = matches.map(Number);
        const date = new Date(year, month - 1, day);
        return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
    }

    getNbrDaysInMonth() {
        const year = this.currentMonthDisplay.getFullYear();
        const month = this.currentMonthDisplay.getMonth();
        const lastDay = new Date(year, month + 1, 0);
        return lastDay.getDate();
    }

    getNbrDaysInPreviousMonth() {
        const prevMonthDisplay = new Date(this.currentMonthDisplay.getFullYear(), this.currentMonthDisplay.getMonth() - 1);
        const year = prevMonthDisplay.getFullYear();
        const month = prevMonthDisplay.getMonth();
        const lastDay = new Date(year, month + 1, 0);
        return lastDay.getDate();
    }


    render() {
        let counterDayCreated = 0;
        this.daysGridContainer.innerHTML = "";
        const nbrDaysInMonth = this.getNbrDaysInMonth();
        const nbrDaysInPrevMonth = this.getNbrDaysInPreviousMonth();
        this.currentMonthDisplay.setDate(1);

        //ternary to fix the non sense of the Americans. Their first day of the week is Sunday.
        const firstDayOfMonth = this.currentMonthDisplay.getDay() === 0 ? 6 : this.currentMonthDisplay.getDay() - 1;

        //empty cells to add at the beginning of the calendar so the first day of the month start at the correct day name.
        const nbrDaysInWeek = 7;
        const emptyCellsToAdd = nbrDaysInWeek - (nbrDaysInWeek - firstDayOfMonth);
        for (let i = emptyCellsToAdd; i > 0; i--) {
            const dayCell = document.createElement("div");
            dayCell.innerHTML = nbrDaysInPrevMonth - i + 1;
            dayCell.classList.add("day_cell", "empty");
            this.daysGridContainer.appendChild(dayCell);
            counterDayCreated++;
        }

        for (let daysCount = 1; daysCount <= nbrDaysInMonth; daysCount++) {
            this.currentMonthDisplay.setDate(daysCount);
            const isToday = this.today.getDate() == this.currentMonthDisplay.getDate() && this.today.getMonth() == this.currentMonthDisplay.getMonth() && this.today.getFullYear() == this.currentMonthDisplay.getFullYear();
            const isSelected = this.selectedDate.getDate() == this.currentMonthDisplay.getDate() && this.selectedDate.getMonth() == this.currentMonthDisplay.getMonth() && this.selectedDate.getFullYear() == this.currentMonthDisplay.getFullYear();
            const isWeekend = this.currentMonthDisplay.getDay() == 0 || this.currentMonthDisplay.getDay() == 6;
            const dayCell = document.createElement("button");
            dayCell.type = "button";
            dayCell.innerHTML = daysCount;
            dayCell.classList.add("day_cell");
            if (isToday) {
                dayCell.classList.add("today");
            }

            if (isSelected) {
                dayCell.classList.add("selected");
            }

            if (isWeekend) {
                dayCell.classList.add("week_end");
            }

            dayCell.setAttribute("data-day", daysCount.toString());
            this.daysGridContainer.appendChild(dayCell);
            counterDayCreated++;
        }


        let dayNextMonth = 1;
        const requiredNbrOfDaysToDisplay = 42;
        while (counterDayCreated < requiredNbrOfDaysToDisplay) {
            const dayCell = document.createElement("div");
            dayCell.innerHTML = dayNextMonth;
            dayCell.classList.add("day_cell", "empty");
            this.daysGridContainer.appendChild(dayCell);
            counterDayCreated++;
            dayNextMonth++;
        }
    }

    setSelectedDate(year, month, day, displaySelectedDate = false) {
        this.selectedDate.setDate(day);
        this.selectedDate.setMonth(month);
        this.selectedDate.setFullYear(year);

        if (displaySelectedDate) {
            this.currentMonthDisplay = new Date(year, month, 1);
            this.selectOptionsFromSelectedDate();
            this.render();
            return;
        }

        //if the selected date is the month being currently displayed, update the visual of the selected date
        if (month == this.currentMonthDisplay.getMonth() && year == this.currentMonthDisplay.getFullYear()) {
            const selectedDayCell = this.daysGridContainer.querySelector(".day_cell.selected");
            if (selectedDayCell)
                selectedDayCell.classList.remove("selected");

            this.daysGridContainer.querySelector(`[data-day="${day}"]`).classList.add("selected");
        }


    }
}


const DATE_PICKERS = [];
document.querySelectorAll(".date_picker").forEach((datepicker) => {
    DATE_PICKERS.push(new DatePicker(datepicker));
})
// const calendar = new DatePicker(document.querySelector(".date_picker"));