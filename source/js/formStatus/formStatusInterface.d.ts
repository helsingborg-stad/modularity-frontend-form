type MessageStatus = {
    status: string;
    message: string;
    icon: string;
    progress: number;
    delay?: number;
    showReturn?: boolean;
    showTryAgain?: FormActionInterface|false;
    hideOverlay?: boolean;
}

interface StatusRendererButtonUIHandlerInterface {
    toggleReturnButton(shouldShow?: boolean): void;
    toggleTryAgainButton(shouldShow?: FormActionInterface|false): void;
}