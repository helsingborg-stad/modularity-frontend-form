class GoogleMapValueLoader implements GoogleMapValueLoaderInterface {
	private parent!: GoogleMapInterface;

	public init(parent: GoogleMapInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		// TODO: implement value loading
	}
}

export default GoogleMapValueLoader;
