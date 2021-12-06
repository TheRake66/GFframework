// Librairie Url
class Url {

    /**
	 * Remplace un parametre de l'url
	 * 
	 * @param {string} param le nom du parametre
	 * @param {string} remplace sa nouvelle valeur
	 * @return {string} le nouvel url
     */
    static changeGet(param, remplace) {
        let regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?");
        let query = window.location.search.replace(regex, "$1").replace(/&$/, '');

        return (query.length > 2 ? query + "&" : "?") + (remplace ? param + "=" + remplace : '');
    };


    /**
     * Retourne un paramettre passe en GET
     * 
     * @param {string} name nom du paramettre
     * @returns {string} valeur du paramettre
     */
    static paramGet(name) {
        let queryString = window.location.search;
        let urlParams = new URLSearchParams(queryString);
        return urlParams.get(name);
    }

}