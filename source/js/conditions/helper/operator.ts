export function evaluateCondition(condition: Condition, currentValue: any): boolean {
    switch (condition.operator) {
      case '==':
        return currentValue == condition.value;
      case '===':
        return currentValue === condition.value;
      case '!=':
        return currentValue != condition.value;
      case '!==':
        return currentValue !== condition.value;
      case '>':
        return currentValue > condition.value;
      case '<':
        return currentValue < condition.value;
      case '>=':
        return currentValue >= condition.value;
      case '<=':
        return currentValue <= condition.value;
      default:
        return false;
    }
}