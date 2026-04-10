import ConditionalLogicController from "./conditionalLogicController";

class BasicLayout implements BasicLayoutInterface {
    constructor(
        protected layoutData: LayoutData,
        protected layoutUI: BasicLayoutUIInterface,
        private readonly conditionalLogicController: ConditionalLogicController
    ) {
        this.setConditionalSelectListener();
        this.setConditionalOperatorSelectListener();
        this.setConditionalValueSelectListener();
    }

    public init(): void {
        this.conditionalLogicController.init();
    }

    private setConditionalSelectListener(): void {
        this.layoutUI.onConditionalSelectChange(() => {
            this.conditionalLogicController.handleTargetChange();
            this.layoutData.layout.dispatchEvent(new CustomEvent('layout:conditional-changed', {
                detail: { layoutId: this.layoutData.layoutId }
            }));
        });
    }

    private setConditionalOperatorSelectListener(): void {
        this.layoutUI.onConditionalOperatorSelectChange(() => {
            this.conditionalLogicController.handleOperatorChange();
        });
    }

    private setConditionalValueSelectListener(): void {
        this.layoutUI.onConditionalLogicValueSelectChange(() => {
            this.conditionalLogicController.handleValueChange();
        });
    }

    public getSavedConditionalTargetId(): string {
        return this.conditionalLogicController.getSavedTargetId();
    }

    public getId(): string {
        return this.layoutData.layoutId;
    }

    public getNameField(): HTMLInputElement {
        return this.layoutUI.getNameField();
    }

    public getType(): string {
        return this.layoutData.layoutType;
    }

    public updateConditionalSelectOptions(optionsNodes: Node): void {
        this.conditionalLogicController.renderConditionalSelectOptions(optionsNodes);
    }

    public updateConditionalSelectValuesOptions(optionsNodes: Node, values: OptionValues[]): void {
        this.conditionalLogicController.updateConditionalValueOptions(optionsNodes, values);
    }
}

export default BasicLayout;
