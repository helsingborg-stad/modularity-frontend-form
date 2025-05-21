class HtmlValidator implements ValidationControlInterface {
    private select!: SelectInterface;

    public init(select: SelectInterface) {
        this.select = select;
    }

    public isInvalid(): false|ValidationControlInterface {
        const valid = this.select.getSelect().checkValidity();
        return valid ? false : this;
    }

    public getFailedValidationMessage(): string {
        return this.select.getSelect().validationMessage;
    }
}

export default HtmlValidator;