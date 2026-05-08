type MessageStatus = {
	status: string;
	message: string;
	icon: string;
	progress: number;
	delay?: number;
	showReturn?: FormActionInterface | false;
	showTryAgain?: FormActionInterface | false;
	hideOverlay?: boolean;
};

interface StatusRendererButtonUIHandlerInterface {
	toggleReturnButton(shouldShow?: FormActionInterface | false): void;
	toggleTryAgainButton(shouldShow?: FormActionInterface | false): void;
}
