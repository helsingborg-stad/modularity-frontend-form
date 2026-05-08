class MapValidator implements ValidationControlInterface {
	private map!: GoogleMapInterface;

	constructor(private modularityFrontendFormLang: ModularityFrontendFormLang) {}

	public init(parent: GoogleMapInterface): void {
		this.map = parent;
	}

	public isInvalid(): false | ValidationControlInterface {
		return this.map.isRequired() && !this.map.hasValue() ? this : false;
	}

	public getFailedValidationMessage(): string {
		return (
			this.modularityFrontendFormLang.errorMap ??
			"Please choose a place on the map."
		);
	}
}

export default MapValidator;
