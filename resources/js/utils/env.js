/**
 * Utility functions for the frontend
 */

/**
 * Check if we're in local environment
 * @returns {boolean}
 */
export function isLocalEnv() {
    return import.meta.env.VITE_APP_ENV === 'local';
}
