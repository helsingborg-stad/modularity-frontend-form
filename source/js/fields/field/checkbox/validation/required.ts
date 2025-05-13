class Required implements ValidationControlInterface {

    public init(field: CheckboxInterface) {

    }

    public isInvalid(): false|string {
        return false;
    }

    private getValidationErrorMessage() {

    }

    private getValidationSuccessMessage() {

    }
}

export default Required;