class RowCount implements ValidationControlInterface {
    private repeater!: RepeaterInterface;

    public init(repeater: RepeaterInterface) {
        this.repeater = repeater;
    }

    public isInvalid(): false|ValidationControlInterface {
        const hasRows = this.repeater.hasValue();

        return hasRows ? false : this;
    }

    public getFailedValidationMessage(): string {
        return 'At least one row is required.';
    }
}

export default RowCount;