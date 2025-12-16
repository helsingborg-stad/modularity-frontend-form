import { PlaceObject } from '@helsingborg-stad/openstreetmap';
import GoogleMapValueLoader from './load/googleMapValueLoader';
import { OpenstreetmapInterface } from './openstreetmap/openstreetmapInterface';

class GoogleMap implements GoogleMapInterface {
	private required: boolean = false;
	constructor(
		private field: HTMLElement,
		private hiddenField: HTMLInputElement,
		private openstreetmapInstance: OpenstreetmapInterface,
		private name: string,
		private googleMapValidator: ConditionValidatorInterface,
		private conditionsHandler: ConditionsHandlerInterface,
		private validator: FieldValidatorInterface,
		private loader: GoogleMapValueLoaderInterface = new GoogleMapValueLoader(),
	) {}

	public init(conditionBuilder: ConditionBuilderInterface): void {
		this.required = this.getFieldContainer().hasAttribute('data-js-required');
		this.conditionsHandler.init(this, conditionBuilder);
		this.googleMapValidator.init(this);
		this.openstreetmapInstance.init();
		this.validator.init(this);
		this.loader.init(this);
		this.listenForMarkerEvents();
	}

	public getName(): string {
		return this.name;
	}

	public getConditionsHandler(): ConditionsHandlerInterface {
		return this.conditionsHandler;
	}

	public getConditionValidator(): ConditionValidatorInterface {
		return this.googleMapValidator;
	}

	public getValidator(): FieldValidatorInterface {
		return this.validator;
	}

	public getOpenstreetmap(): OpenstreetmapInterface {
		return this.openstreetmapInstance;
	}

	public getFieldContainer(): HTMLElement {
		return this.field;
	}

	public isRequired(): boolean {
		return this.required;
	}

	public hasValue(): boolean {
		return this.getHiddenField().value.length > 0;
	}

	public getHiddenField(): HTMLInputElement {
		return this.hiddenField;
	}

	public getValueLoader(): GoogleMapValueLoaderInterface {
		return this.loader;
	}

	private listenForMarkerEvents(): void {
		this.openstreetmapInstance.addMarkerMovedListener((placeObject: PlaceObject | null) => {
			this.hiddenField.value = placeObject ? JSON.stringify(placeObject) : '';
			this.conditionsHandler.checkConditions();
			this.validator.validate();
		});
	}
}

export default GoogleMap;
