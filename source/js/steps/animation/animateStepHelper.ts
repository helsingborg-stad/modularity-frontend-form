class AnimateStepHelper {
    private isVisible: string = 'is-visible';
    private static prevHeight: string = '0px';
    private originalHeight: string = '0px';

    constructor(private container: HTMLElement) {

    }
    public preHide(): void {
        AnimateStepHelper.prevHeight = this.getHeight();
    }

    public preShow(): void {
        this.container.style.display = 'block';
    
        this.container.offsetHeight;
    
        requestAnimationFrame(() => {
            this.originalHeight = this.getHeight();
            this.container.style.minHeight = AnimateStepHelper.prevHeight;
            this.container.style.maxHeight = AnimateStepHelper.prevHeight;
            this.container.style.position = 'absolute';
        });
    }

    public show(): void {
        requestAnimationFrame(() => {
            const handleTransitionEnd = () => {
                this.container.removeEventListener('transitionend', handleTransitionEnd);
                this.container.style.minHeight = '';
                this.container.style.maxHeight = '';
            };
    
            this.container.style.position = 'relative';
            this.container.style.minHeight = this.originalHeight;
            this.container.style.maxHeight = this.originalHeight;
            this.container.addEventListener('transitionend', handleTransitionEnd, { once: true });
            this.container.classList.add(this.isVisible);
        });
    }

    public hide(): Promise<void> {
        return new Promise((resolve) => {
            const handleTransitionEnd = (event: TransitionEvent) => {
                this.container.removeEventListener('transitionend', handleTransitionEnd);
                this.container.style.display = 'none';
                this.container.style.maxHeight = '0px';
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