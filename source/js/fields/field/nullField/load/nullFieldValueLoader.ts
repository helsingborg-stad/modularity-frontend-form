class NullFieldValueLoader implements FieldValueLoaderInterface {
	public load(value: any): void {
		return;
	}
}

export default NullFieldValueLoader;
