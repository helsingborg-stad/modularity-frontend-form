class StatusRendererButtonUIHandlerInterface implements StatusRendererButtonUIHandlerInterface {
    private tryAgainOnclick: FormActionInterface|false = false;
    constructor(
        private returnButton: HTMLElement,
        private tryAgainButton: HTMLElement
    ) {}

    public setup(): this {
        this.returnToFormListener();
        this.tryAgainListener();
        return this;
    }

    public toggleReturnButton(shouldShow: boolean = false): void {
        this.returnButton.classList.toggle('u-display--none', !shouldShow);
    }

    public toggleTryAgainButton(shouldShow: FormActionInterface|false = false): void {
        this.tryAgainOnclick = shouldShow;

        this.tryAgainButton.classList.toggle('u-display--none', !shouldShow);
    }

    private tryAgainListener(): void {
        this.tryAgainButton.addEventListener('click', (e) => {
            e.preventDefault();

            if (this.tryAgainOnclick) {
                this.tryAgainOnclick.retry();
            }
        });
    }

    private returnToFormListener(): void {
        this.returnButton.addEventListener('click', () => {
            window.location.reload();
        })
    }
}

export default StatusRendererButtonUIHandlerInterface;