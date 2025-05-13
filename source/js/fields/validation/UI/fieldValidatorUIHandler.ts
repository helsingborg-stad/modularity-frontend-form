class FieldValidatorUIHandler implements FieldValidatorUIHandlerInterface {
    private field!: FieldInterface;
    private activeNotice: HTMLElement|null = null;

    constructor(private notices: NoticeInterface) {}

    public init(field: FieldInterface) {
        this.field = field;
    }

    public addInvalidNotice(message: string): void {
        if (this.activeNotice) {
            return;
        }

        const notice = this.notices.getErrorNotice(message)
        this.field.getField().prepend(notice);
        this.activeNotice = notice;
    }

    public removeInvalidNotice(): void {
        if (this.activeNotice) {
            this.activeNotice.remove();
            this.activeNotice = null;
        }
    }
}

export default FieldValidatorUIHandler;