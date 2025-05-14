class FieldValidatorUIHandler implements FieldValidatorUIHandlerInterface {
    private field!: FieldInterface;
    private activeNotice: HTMLElement|null = null;
    private invalidClass: string = 'is-invalid';

    constructor(private notices: NoticeInterface) {}

    public init(field: FieldInterface) {
        this.field = field;
    }

    public addInvalidNotice(message: string): void {
        if (this.activeNotice) {
            return;
        }

        const notice = this.notices.getErrorNotice(message)
        this.field.getField().classList.add(this.invalidClass);
        this.field.getField().prepend(notice);
        this.activeNotice = notice;
    }

    public removeInvalidNotice(): void {
        if (this.activeNotice) {
            this.field.getField().classList.remove(this.invalidClass);
            this.activeNotice.remove();
            this.activeNotice = null;
        }
    }
}

export default FieldValidatorUIHandler;