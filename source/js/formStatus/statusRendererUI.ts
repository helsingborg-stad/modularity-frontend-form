class StatusRendererUI {
    constructor(
        private icon: HTMLElement

    ) {}

    /**
     * Changes the iconName with transition effect.
     */
    public updateIcon(iconName: string): void {
        const currentIcon = this.icon.getAttribute('data-material-symbol') || '';
        const [currentBase] = currentIcon.split('_');
        const [newBase] = iconName.split('_');
        if (currentBase !== newBase) {
            this.icon.style.transition = 'opacity 0.3s';
            this.icon.style.opacity = '0';
            setTimeout(() => {
                this.icon.setAttribute('data-material-symbol', iconName);
                this.icon.style.opacity = '1';
            }, 300);
        } else {
            this.icon.setAttribute('data-material-symbol', iconName);
        }
    }
}

export default StatusRendererUI;