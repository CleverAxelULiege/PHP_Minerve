/**
 * @typedef {Object} User
 * @property {number} id
 * @property {string|null} ulgId
 * @property {string|null} lastname
 * @property {string|null} firstname
 * @property {string|null} surname
 * @property {string|null} email
 * @property {string|null} phoneNumber
 * @property {string|null} personalDirectory
 * @property {string|null} comments
 * @property {boolean} isReachable
 */


//cache it if any other script call UserApiCall GetAll
let __users = null;
export class UserApiCall {
    /**
     * @param {AbortSignal} abortSignal 
     * @returns {Promise<null|User[]>}
     */
    static async getAll(abortSignal) {
        if(__users)
            return __users;

        if(!abortSignal) {
            throw new Error("No abort signal given.");
        }

        try {
            let res = await fetch(`./api/user`, {signal: abortSignal});
            if(!res.ok){
                return null;
            }
            let json = await res.json();
            __users = json;
            return json;
        } catch {
            return null;
        }
        
    }
}