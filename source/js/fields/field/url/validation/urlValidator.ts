class UrlValidator implements ValidationControlInterface {
    private basic!: BasicInterface;

    public init(basic: BasicInterface) {
        this.basic = basic;
    }

    public isInvalid(): false|ValidationControlInterface {
        const valid = this.basic.getInput().checkValidity();

        return valid ? false : this;
    }

    // TODO: Translate
    public getFailedValidationMessage(): string {
        return 'HTML validation failed';
    }
}

export default UrlValidator;