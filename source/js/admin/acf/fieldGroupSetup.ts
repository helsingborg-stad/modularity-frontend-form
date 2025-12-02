import $ from "jquery";
import PostTypeSelect from "./postTypeSelect";
import FieldGroupSelect from "./fieldGroupSelect";
declare const acf: any;

class FieldGroupSetup implements AcfSelectsInterface {
	private ACF_GROUP_SELECT_NAME = "formStepGroup";

	public constructor(
		private store: StoreInterface,
		private fetchGroups: FetchGroupsInterface,
	) {}

	/**
	 * Initializes ACF field listeners for loading and appending fields.
	 */
	public init(): void {
		acf.addAction(
			`load_field/name=${this.ACF_GROUP_SELECT_NAME}`,
			(field: AcfField) => {
				this.initFields(field);
			},
		);

		acf.addAction(
			`append_field/name=${this.ACF_GROUP_SELECT_NAME}`,
			(field: AcfField) => {
				this.initFields(field);
			},
		);
	}

	/**
	 * Stores the ACF field instance in the field storage.
	 *
	 * @param field
	 */
	private initFields(field: AcfField): void {
		const result = this.getGroupId(field);
		if (!result) return;
		const [groupElement, groupId] = result;

		if (!this.store.get(groupId)) {
			const postTypeSelect = this.getPostTypeSelect(groupElement);

			if (!postTypeSelect) {
				console.error(
					`Missing post type select element for group: ${groupElement[0]}`,
				);
				return;
			}

			const fieldStorage = this.store.set(groupId, {
				id: groupId,
				postTypeSelect: null,
				group: groupElement[0],
				fields: [],
			});

			this.store.setPostTypeSelect(
				fieldStorage.id,
				PostTypeSelect.createInstance(
					this.store,
					postTypeSelect,
					fieldStorage.id,
				),
			);
		}

		this.store.addFieldToGroup(
			groupId,
			FieldGroupSelect.createInstance(
				this.store,
				this.fetchGroups,
				field.$el[0],
				groupId,
			),
		);
	}

	private getPostTypeSelect(
		groupElement: JQuery<HTMLElement>,
	): HTMLElement | null {
		const postTypeSelect = acf.getFields({
			name: "saveToPostType",
			parent: groupElement,
		});

		return postTypeSelect &&
			postTypeSelect.length > 0 &&
			postTypeSelect[0].$el[0]
			? postTypeSelect[0].$el[0]
			: null;
	}

	/**
	 * Gets the group ID and element for a given ACF field.
	 *
	 * @param field
	 * @returns
	 */
	private getGroupId(field: AcfField): null | [JQuery<HTMLElement>, string] {
		if (!field.$el[0]) {
			console.error("ACF field element not found.");
			return null;
		}

		const fieldGroupElement = field.$el.closest('[id^="acf-group_"]');

		if (!fieldGroupElement) {
			console.error("ACF field group not found.");
			return null;
		}

		const groupId = fieldGroupElement[0].id;

		return [fieldGroupElement, groupId];
	}
}

export default FieldGroupSetup;
