class Step implements StepInterface {
    private displayBlockClass: string = 'u-display--block';

    constructor(private container: HTMLElement, private index: number) {
    }

    public show(): void {
        this.container.classList.add(this.displayBlockClass);
    }

    public hide(): void {
        this.container.classList.remove(this.displayBlockClass);
    }
}

export default Step;