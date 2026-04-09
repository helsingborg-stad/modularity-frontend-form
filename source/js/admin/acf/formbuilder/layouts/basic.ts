import LayoutOptionFragments from "../layoutOptionFragments";
import {
    createEmptyConditionalLogicState,
    isSameConditionalLogicValues,
    parseConditionalLogicState,
    stringifyConditionalLogicState,
    type ConditionalLogicState
} from "../conditionalLogicState";

class BasicLayout implements BasicLayoutInterface {
    private conditionalLogicState: ConditionalLogicState = createEmptyConditionalLogicState();

    constructor(
        protected layoutData: LayoutData,
        protected layoutUI: BasicLayoutUIInterface
    ) {
        this.setConditionalSelectListener();
        this.setConditionalOperatorSelectListener();
        this.setConditionalValueSelectListener();
    }

    public init(): void {
        this.loadConditionalLogicState();
        this.applyConditionalLogicStateToUi();
        this.updateConditionalState();
        this.saveConditionalLogicState();
    }

    private setConditionalSelectListener(): void {
        this.layoutUI.onConditionalSelectChange(() => {
            this.syncConditionalLogicStateFromUi();
            this.saveConditionalLogicState();
            this.updateConditionalState();
            this.layoutData.layout.dispatchEvent(new CustomEvent('layout:conditional-changed', {
                detail: { layoutId: this.layoutData.layoutId }
            }));
        });
    }

    private setConditionalOperatorSelectListener(): void {
        this.layoutUI.onConditionalOperatorSelectChange(() => {
            this.syncConditionalLogicStateFromUi();
            this.updateConditionalValueSelectDisabledState();
            this.saveConditionalLogicState();
        });
    }

    private setConditionalValueSelectListener(): void {
        this.layoutUI.onConditionalLogicValueSelectChange(() => {
            this.syncConditionalLogicStateFromUi();
            this.saveConditionalLogicState();
        });
    }

    private updateConditionalState(): void {
        this.maybeDisableConditionalOperatorSelect();
        this.updateConditionalOperatorSelectOptions();
        this.updateConditionalValueSelectDisabledState();
        this.clearConditionalValueSelectIfTargetIsNotSelectable();
    }

    private clearConditionalValueSelectIfTargetIsNotSelectable(): void {
        const conditionalTargetLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());

        if (this.isSelectableValuesLayout(conditionalTargetLayout)) {
            return;
        }

        this.layoutUI.clearConditionalLogicValueSelect();
        this.syncConditionalLogicStateFromUi();
        this.saveConditionalLogicState();
    }

    private updateConditionalValueSelectDisabledState(): void {
        const isContainsOperator = this.layoutUI.getConditionalOperatorValue() === '==contains';
        this.layoutUI.setConditionalLogicValueSelectDisabled(!isContainsOperator);
    }

    private isSelectableValuesLayout(layout: BasicLayoutInterface | null): layout is SelectableValuesLayoutInterface {
        return !!layout && layout.getType() === 'radio' && 'getValues' in layout;
    }

    private maybeDisableConditionalOperatorSelect(): void {
        this.layoutUI.setConditionalOperatorSelectDisabled(!this.getSavedConditionalLogicValue());
    }

    private updateConditionalOperatorSelectOptions(): void {
        const conditionalValueLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());
        const includeContains = this.isSelectableValuesLayout(conditionalValueLayout);
        const operatorOptions = LayoutOptionFragments.createConditionalOperatorOptionsFragment(includeContains);
        const selectedOperator = this.conditionalLogicState.operator;

        this.layoutUI.renderConditionalOperatorSelectOptions(operatorOptions, selectedOperator);
    }

    private loadConditionalLogicState(): void {
        const parsedState = parseConditionalLogicState(this.layoutUI.getSavedConditionalLogicValue());

        this.conditionalLogicState = parsedState ?? createEmptyConditionalLogicState();
    }

    private applyConditionalLogicStateToUi(): void {
        if (this.conditionalLogicState.targetId) {
            this.layoutUI.setConditionalSelectValue(this.conditionalLogicState.targetId);
        }

        if (this.conditionalLogicState.operator) {
            this.layoutUI.setConditionalOperatorValue(this.conditionalLogicState.operator);
        }

        if (this.conditionalLogicState.value.length > 0) {
            this.layoutUI.setConditionalLogicValueSelectValues(this.conditionalLogicState.value);
        }
    }

    private syncConditionalLogicStateFromUi(): void {
        this.conditionalLogicState = {
            targetId: this.layoutUI.getConditionalSelectValue(),
            operator: this.layoutUI.getConditionalOperatorValue(),
            value: this.layoutUI.getConditionalLogicValueSelectValues()
        };
    }

    private saveConditionalLogicState(): void {
        this.layoutUI.setSavedConditionalLogicValue(stringifyConditionalLogicState(this.conditionalLogicState));
    }

    public getSavedConditionalLogicValue(): string {
        return this.conditionalLogicState.targetId;
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
        this.layoutUI.renderConditionalSelectOptions(optionsNodes, this.layoutData.layoutId, this.getSavedConditionalLogicValue());
    }

    public updateConditionalSelectValuesOptions(optionsNodes: Node, values: OptionValues[]): void {
        const selectedValues = this.conditionalLogicState.value;

        this.layoutUI.renderConditionalSelectValuesOptions(
            optionsNodes,
            selectedValues,
            values
        );

        const selectedValuesAfterRender = this.layoutUI.getConditionalLogicValueSelectValues();

        if (!isSameConditionalLogicValues(selectedValuesAfterRender, selectedValues)) {
            this.conditionalLogicState.value = selectedValuesAfterRender;
            this.saveConditionalLogicState();
        }
    }
}

export default BasicLayout;