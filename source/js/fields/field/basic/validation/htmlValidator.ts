class HtmlValidator implements ValidationControlInterface {
    private basic!: BasicInterface;

    public init(basic: BasicInterface) {
        this.basic = basic;
    }

    public isInvalid(): false|ValidationControlInterface {
        const valid = this.basic.getInput().checkValidity();

        return valid ? false : this;
    }

    // TODO: For today
    public getFailedValidationMessage(): string {
        return 'HTML validation failed';
    }
}

export default HtmlValidator;