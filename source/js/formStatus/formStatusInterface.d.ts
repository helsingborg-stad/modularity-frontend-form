type MessageStatus = {
    status: string;
    message: string;
    icon: string;
    progress: number;
    delay?: number;
    showReturn?: boolean;
    showTryAgain?: boolean;
}