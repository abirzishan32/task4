import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['time']
    static values = {
        timestamp: String
    }

    connect() {
        this.updateTime();
        this.interval = setInterval(() => this.updateTime(), 60000); // Update every minute
    }

    disconnect() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

    updateTime() {
        const date = new Date(this.timestampValue);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        this.timeTarget.textContent = this.formatTimeAgo(seconds);
    }

    formatTimeAgo(seconds) {
        if (seconds < 60) {
            return 'Just now';
        }

        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) {
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        }

        const hours = Math.floor(minutes / 60);
        if (hours < 24) {
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        }

        const days = Math.floor(hours / 24);
        if (days < 30) {
            return `${days} day${days > 1 ? 's' : ''} ago`;
        }

        const months = Math.floor(days / 30);
        if (months < 12) {
            return `${months} month${months > 1 ? 's' : ''} ago`;
        }

        const years = Math.floor(days / 365);
        return `${years} year${years > 1 ? 's' : ''} ago`;
    }
} 