export default class DOMStuff {

    static createPasswordInput() {
        const label = document.createElement('label');
        label.textContent = "Password";
        label.setAttribute("for", "password")

        const img = document.createElement('img');
        img.src = "../libs/images/auth/lock-icon.svg";
        img.classList.add('passwordVis');

        const input = document.createElement('input');
        input.type = 'password';
        input.id = 'password';
        input.name = 'password';
        input.placeholder = "Password";
        input.required = true;

        return [label, img, input];
    }
    
    static createUsernameInput() {
        const label = document.createElement('label');
        label.textContent = "Username";
        label.setAttribute("for", "username")

        const img = document.createElement('img');
        img.src = "../libs/images/auth/user-icon.svg";

        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'username';
        input.name = 'username';
        input.placeholder = "Username";
        input.maxLength = 30;
        input.required = true;

        return [label, img, input];
    }
}