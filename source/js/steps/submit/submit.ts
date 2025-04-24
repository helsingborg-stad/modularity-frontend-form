class Submit implements SubmitInterface {
    constructor(
        private form: HTMLFormElement
    ) {
    }

    public submit(): void {
        console.log("submiting");
    }
}

export default Submit;