import sha256 from 'crypto-js/sha256';

export const signRequest = (path, timestamp, content, key) => {
  return sha256(`${path}|${timestamp}|${content}|${key}`).toString();
};
