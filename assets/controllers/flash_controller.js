import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['message'];
    static values = {
        timeout: Number
    }

    connect() {
        if (this.hasTimeoutValue) {
            setTimeout(() => {
                this.close();
            }, this.timeoutValue);
        }
    }

    close() {
        this.messageTarget.classList.remove('translate-x-0');
        this.messageTarget.classList.add('translate-x-full');
        
        setTimeout(() => {
            this.element.remove();
        }, 500);
    }
} 