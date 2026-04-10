import { createConditionalOperatorOptionsFragment } from "../../layoutOptionFragments";

class ConditionalLogicController {
    constructor(
        private readonly layoutData: LayoutData,
        private readonly layoutUI: BasicLayoutUIInterface
    ) { }

    public init(): void {
        this.restoreState();
        this.syncFields();
    }

    public handleTargetChange(): void {
        this.syncFields();
        this.saveState();
    }

    public handleOperatorChange(): void {
        this.syncValueField();
        this.saveState();
    }

    public handleValueChange(): void {
        this.saveState();
    }

    public getSavedTargetId(): string {
        return this.getSavedState()?.targetId ?? '';
    }

    public renderConditionalSelectOptions(optionsNodes: Node): void {
        this.layoutUI.renderConditionalSelectOptions(optionsNodes, this.layoutData.layoutId, this.getSavedTargetId());
    }

    public updateConditionalValueOptions(optionsNodes: Node, values: OptionValues[]): void {
        const selectedValues = this.getSavedState()?.value ?? this.layoutUI.getConditionalLogicValueSelectValues();

        this.layoutUI.renderConditionalSelectValuesOptions(optionsNodes, selectedValues, values);

        const renderedValues = this.layoutUI.getConditionalLogicValueSelectValues();

        if (!this.hasSameValues(renderedValues, selectedValues)) {
            this.saveState();
        }
    }

    private syncFields(): void {
        this.syncOperatorField();
        this.syncValueField();
    }

    private syncOperatorField(): void {
        this.layoutUI.setConditionalOperatorSelectDisabled(!this.layoutUI.getConditionalSelectValue());

        const targetLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());
        const includeContains = this.isValueProvidingLayout(targetLayout);
        const operatorOptions = createConditionalOperatorOptionsFragment(includeContains);

        this.layoutUI.renderConditionalOperatorSelectOptions(operatorOptions, this.layoutUI.getConditionalOperatorValue());
    }

    private syncValueField(): void {
        const isContainsOperator = this.layoutUI.getConditionalOperatorValue() === '==contains';
        this.layoutUI.setConditionalLogicValueSelectDisabled(!isContainsOperator);

        const targetLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());

        if (!this.isValueProvidingLayout(targetLayout)) {
            this.layoutUI.clearConditionalLogicValueSelect();
        }
    }

    private restoreState(): void {
        const state = this.getSavedState();

        if (!state) {
            return;
        }

        if (state.targetId) {
            this.layoutUI.setConditionalSelectValue(state.targetId);
        }

        if (state.operator) {
            this.layoutUI.setConditionalOperatorValue(state.operator);
        }

        if (state.value.length > 0) {
            this.layoutUI.setConditionalLogicValueSelectValues(state.value);
        }
    }

    private saveState(): void {
        const state: ConditionalLogicState = {
            targetId: this.layoutUI.getConditionalSelectValue(),
            operator: this.layoutUI.getConditionalOperatorValue(),
            value: this.layoutUI.getConditionalLogicValueSelectValues()
        };

        this.layoutUI.setSavedConditionalLogicValue(JSON.stringify(state));
    }

    private getSavedState(): ConditionalLogicState | null {
        const rawValue = this.layoutUI.getSavedConditionalLogicValue();

        if (!rawValue) {
            return null;
        }

        try {
            const parsed = JSON.parse(rawValue) as Partial<ConditionalLogicState>;

            if (typeof parsed !== 'object' || parsed === null) {
                return null;
            }

            const value = Array.isArray(parsed.value)
                ? parsed.value
                : typeof parsed.value === 'string'
                    ? [parsed.value]
                    : [];

            return {
                targetId: parsed.targetId ?? '',
                operator: parsed.operator ?? '',
                value
            };
        } catch {
            return null;
        }
    }

    private isValueProvidingLayout(layout: BasicLayoutInterface | null): layout is SelectableValuesLayoutInterface {
        return !!layout && 'getValue' in layout;
    }

    private hasSameValues(a: ConditionalLogicValue, b: ConditionalLogicValue): boolean {
        if (a.length !== b.length) {
            return false;
        }

        const sortedA = [...a].sort();
        const sortedB = [...b].sort();

        return sortedA.every((value, index) => value === sortedB[index]);
    }
}

export default ConditionalLogicController;