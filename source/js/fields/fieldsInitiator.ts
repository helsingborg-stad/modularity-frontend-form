class FieldsInitiator implements FieldsInitiatorInterface {
	private fieldBuilder: FieldBuilderInterface | null = null;

	/**
	 * Constructor
	 * @param fieldBuilder FieldBuilderInterface
	 */
	public init(fieldBuilder: FieldBuilderInterface): void {
		this.fieldBuilder = fieldBuilder;
	}

	/**
	 * Setup the conditionals for the fields
	 *
	 * Note: This method is called after fields that are used have been built
	 * and initialized. It sets up the conditionals for given fields.
	 * @param fields FieldsObject
	 */
	public initializeConditionals(fields: FieldsObject): void {
		if (!this.fieldBuilder) {
			console.error("FieldsInitiator is not initialized");
			return;
		}

		for (const fieldName in fields) {
			this.fieldBuilder
				.getFieldsObject()
				[fieldName].getConditionsHandler()
				.getConditions()
				.forEach((condition) => {
					condition.getConditionFieldNames().forEach((conditionFieldName) => {
						if (this.fieldBuilder!.getFieldsObject()[conditionFieldName]) {
							this.fieldBuilder!.getFieldsObject()
								[conditionFieldName].getConditionsHandler()
								.addValueChangeListener(
									this.fieldBuilder!.getFieldsObject()[fieldName],
								);
						}
					});
				});
		}

		for (const fieldName in fields) {
			this.fieldBuilder
				.getFieldsObject()
				[fieldName].getConditionsHandler()
				.checkConditions();
		}
	}
}

export default FieldsInitiator;
