class HtmlValidator implements ValidationControlInterface {
    private basic!: BasicInterface;

    public init(basic: BasicInterface) {
        this.basic = basic;
    }

    public isInvalid(): false|ValidationControlInterface {
        const valid = this.basic.getInput().checkValidity();

        return valid ? false : this;
    }

    public getFailedValidationMessage(): string {
        return this.basic.getInput().validationMessage;
    }
}

export default HtmlValidator;