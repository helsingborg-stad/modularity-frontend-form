class HtmlValidator implements ValidationControlInterface {
    private select!: SelectInterface;

    public init(select: SelectInterface) {
        this.select = select;
    }

    public isInvalid(): false|ValidationControlInterface {
        const valid = this.select.getField().checkValidity();
        return valid ? false : this;
    }

    public getFailedValidationMessage(): string {
        return this.select.getField().validationMessage;
    }
}

export default HtmlValidator;