/**
 * @typedef {Object} MessageAuthor
 * @property {number} id
 * @property {string} firstName
 * @property {string} lastName
 * @property {string} ulgId
 */

/**
 * @typedef {Object} Message
 * @property {number} id
 * @property {string} message
 * @property {boolean} isPublic
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {MessageAuthor} author
 */

/**
 * @typedef {Object} Helper
 * @property {number} id
 * @property {string} surname
 */

/**
 * @typedef {Object} Service
 * @property {number} id
 * @property {string} name
 */

/**
 * @typedef {Object} Intervention
 * @property {number} id
 * @property {string} requestDate - Format: YYYY-MM-DD HH:MM:SS
 * @property {string} updatedAt - Format: YYYY-MM-DD HH:MM:SS
 * @property {string} requestIp
 * @property {number} requesterUserId
 * @property {number} interventionTargetUserId
 * @property {?number} lockedByUserId
 * @property {number} interventionSubtypeId
 * @property {number} interventionTypeId
 * @property {string} status
 * @property {?string} description
 * @property {string} title
 * @property {number} materialId
 * @property {?string} interventionDate
 * @property {?string} comments
 * @property {?string} solution
 * @property {string} materialName
 * @property {number} targetUserId
 * @property {string} targetUserName
 * @property {string} requesterUserName
 * @property {number} subtypeId
 * @property {string} subtypeName
 * @property {number} typeId
 * @property {string} typeName
 * @property {Helper[]} helpers
 * @property {Service[]} services
 * @property {{id:number, name:string}[]} keywords
 * @property {Message[]} messages
 */




export class InterventionApiCall {
    /**
     * @param {number} id 
     * @param {AbortSignal} abortSignal 
     * @returns {Promise<null|Intervention>}
     */
    static async getInterventionById(id, abortSignal) {
        if(!abortSignal) {
            throw new Error("No abort signal given.");
        }

        try {
            let res = await fetch(`./api/intervention/${id}`, {signal: abortSignal});
            if(!res.ok){
                return null;
            }
            let json = await res.json();

            return json;
        } catch {
            return null;
        }
        
    }
}