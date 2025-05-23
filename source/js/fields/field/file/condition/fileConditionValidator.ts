class FileConditionValidator implements ConditionValidatorInterface {
    private parent: BasicInterface|null = null;

    public init(parent: BasicInterface): void {
        this.parent = parent;
    }

    public validate(condition: any): boolean {
        if (!this.parent?.getField().files) {
            console.error('No files found in input element');
            return false;
        }

        const value = this.parent.getField().files!.length > 0;

        switch (condition.operator) {
            case '==':
            case '=':
            case '===':
            case '!=empty':
            case '==contains':
                return value === true;
            case '!=':
            case '!==':
            case '==empty':
            case '!=contains':
                return value === false;
            default:
                console.error('Invalid operator:', condition.operator);
                return false;
        }

    }
}

export default FileConditionValidator;