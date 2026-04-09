export type ConditionalLogicState = {
    targetId: string;
    operator: string;
    value: ConditionalLogicValue;
};

export function createEmptyConditionalLogicState(): ConditionalLogicState {
    return {
        targetId: '',
        operator: '',
        value: []
    };
}

export function parseConditionalLogicState(rawValue: string): ConditionalLogicState | null {
    if (!rawValue) {
        return null;
    }

    try {
        const parsedValue = JSON.parse(rawValue) as Partial<ConditionalLogicState>;

        if (typeof parsedValue !== 'object' || parsedValue === null) {
            return null;
        }

        const value = Array.isArray(parsedValue.value)
            ? parsedValue.value
            : [];

        return {
            targetId: parsedValue.targetId ?? '',
            operator: parsedValue.operator ?? '',
            value
        };
    } catch {
        return null;
    }
}

export function stringifyConditionalLogicState(state: ConditionalLogicState): string {
    return JSON.stringify(state);
}

export function isSameConditionalLogicValues(a: ConditionalLogicValue, b: ConditionalLogicValue): boolean {
    if (a.length !== b.length) {
        return false;
    }

    const sortedA = [...a].sort();
    const sortedB = [...b].sort();

    return sortedA.every((value, index) => value === sortedB[index]);
}
