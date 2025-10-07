class Wysiwyg implements WysiwygInterface {
    private required: boolean = false;

    constructor(
        private field: HTMLElement,
        private hiddenField: HTMLInputElement,
        private name: string,
        private editor: EditorInterface,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.getFieldContainer().hasAttribute('data-js-required');
        this.addChangeListener();
    }

    public getName(): string {
        return this.name;
    }

    public getFieldContainer(): HTMLElement {
        return this.field;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.conditionValidator;
    }

    public getEditor(): EditorInterface {
        return this.editor;
    }

    public getValidator(): FieldValidatorInterface {
        return this.validator;
    }

    public isRequired(): boolean {
        return this.required;
    }

    public hasValue(): boolean {
        return this.editor.hasContent();
    }

    public getHiddenField(): HTMLInputElement {
        return this.hiddenField;
    }

    private addChangeListener(): void {
        this.editor.addChangeListeners((html: string) => {
            this.hiddenField.value = html;
        });
    }
}

export default Wysiwyg;