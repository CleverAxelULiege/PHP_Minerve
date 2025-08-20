export class ColorHelper {
  // --- Public API ---
  static colorForValues(...values) {
    const hash = this.fnv1a32(this.fingerprint(...values));
    return this.colorFromHash(hash);
  }

  /**
   * @private
   * @param {*} str 
   * @returns 
   */
  static fnv1a32(str) {
    let h = 0x811c9dc5;
    for (let i = 0; i < str.length; i++) {
      h ^= str.charCodeAt(i);
      h = (h + ((h << 1) + (h << 4) + (h << 7) + (h << 8) + (h << 24))) >>> 0;
    }
    return h >>> 0;
  }

  /**
   * @private
   * @param  {...any} values 
   * @returns 
   */
  static fingerprint(...values) {
    const norm = v => String(v ?? "").trim().toLowerCase();
    return values.map(norm).join("|");
  }

  /**
   * @private
   * @param {*} hash 
   * @param {*} opts 
   * @returns 
   */
  static colorFromHash(hash, opts = {}) {
    const {
      saturation = 65,
      lightness = 55,
      hueRange = [0, 360],
    } = opts;

    const [start, end] = hueRange;
    const hue = start + (hash % (end - start));

    const s = Math.max(30, Math.min(85, saturation + (((hash >>> 8) & 0x1F) - 16)));
    const l = Math.max(35, Math.min(70, lightness + (((hash >>> 16) & 0x1F) - 16)));

    const hsl = `hsl(${hue}, ${s}%, ${l}%)`;
    const hex = this.hslToHex(hue, s, l);
    return { hsl, hex, hue, saturation: s, lightness: l };
  }

  /**
   * @private
   * @param {*} h 
   * @param {*} s 
   * @param {*} l 
   * @returns 
   */
  static hslToHex(h, s, l) {
    s /= 100; l /= 100;
    const k = n => (n + h / 30) % 12;
    const a = s * Math.min(l, 1 - l);
    const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
    const toHex = x => Math.round(255 * x).toString(16).padStart(2, "0");
    return `#${toHex(f(0))}${toHex(f(8))}${toHex(f(4))}`;
  }
}


