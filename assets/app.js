import 'bootstrap';

import './styles/app.scss';



function updateEventCountdowns() {
    const countdownElements = document.querySelectorAll('.event-countdown');

    countdownElements.forEach(element => {
        const startDateString = element.dataset.startDate;
        if (!startDateString) return;

        const startDate = new Date(startDateString);
        const now = new Date();
        const diff = startDate - now;

        if (diff <= 0 || isNaN(startDate)) {
            element.innerHTML = '<span class="badge bg-danger">Trwa / Zakończone</span>';
            if (element.intervalId) { clearInterval(element.intervalId); }
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        let countdownText = '<span class="badge bg-warning text-dark">Zostało: ';
        if (days > 0) {
            countdownText += `${days}d ${hours}g `;
        } else if (hours > 0) {
            countdownText += `${hours}g ${minutes}m `;
        } else if (minutes > 0) {
            countdownText += `${minutes}m ${seconds}s`;
        } else {
            countdownText += `${seconds}s`;
        }
        countdownText += '</span>';

        element.innerHTML = countdownText;

        if (!element.intervalId) {
        }
    });
}


function autoDismissVisibleAlerts() {
    const autoDismissAlerts = document.querySelectorAll('.alert-success, .alert-info, .alert-warning');
    const dismissTimeout = 5000; // 5 sekund

    autoDismissAlerts.forEach(alertElement => {
        if (alertElement.offsetParent !== null) {
            if (!alertElement.dismissTimerId) {
                alertElement.dismissTimerId = setTimeout(() => {
                    const alertInstance = bootstrap.Alert.getOrCreateInstance(alertElement);
                    if (alertInstance) {
                        alertInstance.close();
                    }
                }, dismissTimeout);
            }
        } else {
            if (alertElement.dismissTimerId) {
                clearTimeout(alertElement.dismissTimerId);
                delete alertElement.dismissTimerId;
            }
        }
    });
}


document.addEventListener('DOMContentLoaded', () => {

    updateEventCountdowns(); // Pierwsze wywołanie
    setInterval(updateEventCountdowns, 1000);


    autoDismissVisibleAlerts(); // Pierwsze wywołanie dla alertów już obecnych na stronie

});
