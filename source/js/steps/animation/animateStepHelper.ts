class AnimateStepHelper implements AnimateStepHelperInterface {
    private isVisible: string = 'is-visible';

    constructor(private container: HTMLElement) {
    }

    public show(): void {
        const handleTransitionEnd = (event: TransitionEvent) => {
            this.container.removeEventListener('transitionend', handleTransitionEnd);
            this.container.style.maxHeight = '';
        };

        this.container.addEventListener('transitionend', handleTransitionEnd, {once: true});
        this.container.style.maxHeight = this.getHeight();
        this.container.classList.add(this.isVisible);
    }

    public hide(): Promise<void> {
        return new Promise((resolve) => {
            this.container.style.maxHeight = this.getHeight();
            void this.container.offsetHeight;
            const handleTransitionEnd = (event: TransitionEvent) => {
                this.container.removeEventListener('transitionend', handleTransitionEnd);
                this.container.style.maxHeight = '';
                resolve();
            };
    
            this.container.addEventListener('transitionend', handleTransitionEnd, {once: true});
            this.container.classList.remove(this.isVisible);
        });
    }

    private getHeight(): string {
        return `${this.container.scrollHeight}px`;
    }
}

export default AnimateStepHelper;