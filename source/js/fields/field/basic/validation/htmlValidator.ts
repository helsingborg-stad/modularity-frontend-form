class HtmlValidator implements ValidationControlInterface {
    private basic!: BasicInterface;

    public init(basic: BasicInterface) {
        this.basic = basic;
    }

    public isInvalid(): false|ValidationControlInterface {
        const valid = this.basic.getField().checkValidity();
        return valid ? false : this;
    }

    public getFailedValidationMessage(): string {
        return this.basic.getField().validationMessage;
    }
}

export default HtmlValidator;