type MessageStatus = {
    status: string;
    message: string;
    icon: string;
    progress: number;
    delay?: number;
    showReturn?: boolean;
    showTryAgain?: FormActionInterface|false;
}

interface StatusRendererButtonUIHandlerInterface {
    setup(): this;
    toggleReturnButton(shouldShow?: boolean): void;
    toggleTryAgainButton(shouldShow?: FormActionInterface|false): void;
}