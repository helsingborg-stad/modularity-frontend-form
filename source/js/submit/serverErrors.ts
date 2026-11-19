class ServerErrors {
	constructor(private builder: FieldBuilderInterface, private form: HTMLFormElement) {}

	public display(error: any, fallback: string): { messages: string[]; fields: HTMLElement[] } {
		const messages: string[] = [];
		const invalidFields = new Set<FieldInterface>();
		const fields = Object.values(this.builder.getFieldsObject());
		const showFieldErrors = (data: any): string[] => {
			const displayed: string[] = [];
			if (!Array.isArray(data?.field_errors)) return displayed;
			for (const item of data.field_errors) {
				const message = typeof item?.message === "string" && item.message.trim() ? item.message : fallback;
				// Compare actual submitted names, including the repeater row, without selector interpolation.
				const input = Array.from(this.form.querySelectorAll("[name]"))
					.find(input => input.getAttribute("name") === item?.input || input.getAttribute("name") === `${item?.input}[]`);
				const container = input?.closest("[data-js-field]");
				const field = fields.find(field => field.getFieldContainer() === container);
				if (field?.getValidator().setServerError) {
					displayed.push(message);
					if (!invalidFields.has(field)) field.getValidator().setServerError!(message);
					invalidFields.add(field);
				} else {
					messages.push(message);
				}
			}
			return displayed;
		};
		const visit = (error: any) => {
			if (!error || typeof error !== "object") return;
			const displayed = showFieldErrors(error.data);
			if (Array.isArray(error.additional_data)) {
				for (const data of error.additional_data) displayed.push(...showFieldErrors(data));
			}
			if (typeof error.message === "string" && error.message.trim() && !displayed.includes(error.message)) {
				messages.push(error.message);
			}
			if (Array.isArray(error.additional_errors)) error.additional_errors.forEach(visit);
		};
		visit(error);
		return {
			messages: [...new Set(messages)],
			fields: Array.from(invalidFields, field => field.getFieldContainer()),
		};
	}
}

export default ServerErrors;
