class Actionbar implements ActionbarInterface {
    private actionbar!: HTMLElement;
    private buttonList: { [key: string]: HTMLButtonElement } = {};
    constructor(
        private config: EditorConfigInterface,
        private contentAreaInstance: ContentAreaInterface
    ) {
    }

    public appendActionbar(): HTMLElement {
        const div = document.createElement('div');
        div.className = this.config.getClasses().actionbar;

        this.config.getElement().appendChild(div);
        this.actionbar = div;
        return div;
    }

    public getElement(): HTMLElement {
        return this.actionbar;
    }

    public getActionButtons(): { [key: string]: HTMLButtonElement } {
        return this.buttonList;
    }

    public addActionButtons(actions: EditorActions): void {
        for (const key in actions) {
            this.addActionButton(actions[key], key);
        }
    }

    public addActionButton(
        actionButton: EditorAction,
        name: string
    ): HTMLButtonElement {

        const button = this.createActionButton(actionButton);

        button.addEventListener('click', () => {
            this.contentAreaInstance.getElement().focus();
            actionButton.result();
        });

        if (actionButton.state) {
            this.bindActionState(button, actionButton.state);
        }

        this.buttonList[name] = button;
        this.actionbar.appendChild(button);
        return button;
    }

    private createActionButton(actionButton: EditorAction): HTMLButtonElement {
        const button = document.createElement('button');
        button.className = this.config.getClasses().button;
        button.innerHTML = actionButton.icon;
        button.title = actionButton.title;
        button.setAttribute('type', 'button');
        return button;
    }

    private bindActionState(
        button: HTMLButtonElement,
        state: () => boolean|null|undefined
    ): void {
        const update = () => button.classList.toggle(this.config.getClasses().selected, state() ?? undefined);

        this.contentAreaInstance.getElement().addEventListener('keyup', update);
        this.contentAreaInstance.getElement().addEventListener('mouseup', update);
        button.addEventListener('click', update);

        update();
    }
}

export default Actionbar;