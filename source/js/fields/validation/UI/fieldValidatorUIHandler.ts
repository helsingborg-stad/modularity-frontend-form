class FieldValidatorUIHandler implements FieldValidatorUIHandlerInterface {
    private messageElementTarget: string = '[data-js-frontend-form-notice-message]';
    private field!: FieldInterface;
    private activeNotice: HTMLElement|null = null;
    private invalidClass: string = 'is-invalid';
    private currentNoticeMessage: string|null = null;
    private noticeMessageElement: HTMLElement|null = null;
    private errorNotice!: HTMLElement;

    constructor(private notices: NoticeInterface) {
    }
    
    public init(field: FieldInterface) {
        this.errorNotice = this.notices.getErrorNotice();
        this.noticeMessageElement = this.errorNotice.querySelector(this.messageElementTarget);
        this.field = field;
    }

    public addInvalidNotice(message: string): void {
        if (this.activeNotice) {
            return;
        }

        const messageElement = this.errorNotice.querySelector(this.messageElementTarget);

        if (!messageElement) {
            console.error("Message element not found in notice");
            return;
        }

        this.noticeMessageElement = messageElement as HTMLElement;
        this.currentNoticeMessage = message;
        this.noticeMessageElement.innerHTML = message;
        this.field.getFieldContainer().classList.add(this.invalidClass);
        this.field.getFieldContainer().append(this.errorNotice);
        this.activeNotice = this.errorNotice;
    }

    public updateInvalidNotice(message: string): void {
        if (!this.activeNotice || this.currentNoticeMessage === message) {
            return;
        }

        this.noticeMessageElement!.innerHTML = message;
        this.currentNoticeMessage = message;
    }

    public removeInvalidNotice(): void {
        if (this.activeNotice) {
            this.field.getFieldContainer().classList.remove(this.invalidClass);
            this.activeNotice.remove();
            this.activeNotice = null;
        }
    }
}

export default FieldValidatorUIHandler;