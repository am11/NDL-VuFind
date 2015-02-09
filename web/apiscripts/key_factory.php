<?php require_once 'singleton.php';

/**
 * FINNA KeyFactory class
 *
 * This aim of this class is to manage,
 * generate and validate API keys.
 *
 * Note: The current implementation is
 * quite basic.
 *
 */
final class KeyFactory extends Singleton {
    public function generate_key()
    {
        $key = trim(com_create_guid(), '{}');
        // store_key($key);
        return $key;
    }

    public function validate($key, &$token)
    {
        /*
        if(retrieve_key($key, $token))
        {
            $token = generate_token($key);
            return true;
        }
        return false;
        */

        return true;
    }

    private function generate_token($key)
    {
        throw new Exception('Not implemented.');
    }

    private function retrieve_key($key, $token)
    {
        throw new Exception('Not implemented.');
    }

    private function store_key($key)
    {
        throw new Exception('Not implemented.');
    }
}

?>
