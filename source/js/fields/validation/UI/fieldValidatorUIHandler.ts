class FieldValidatorUIHandler implements FieldValidatorUIHandlerInterface {
    private field!: FieldInterface;
    private invalidValidator: ValidationControlInterface|null = null;

    constructor(notices: NoticeInterface) {}

    public init(field: FieldInterface) {
        this.field = field;
    }

    public handleInvalid(invalidValidator: ValidationControlInterface) {
        
    }
}

export default FieldValidatorUIHandler;