<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Google\Cloud\Firestore\FieldPath;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use \Kreait\Firebase\Auth;
use \Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;

class FirebaseLib
{
    protected $auth;
    private $messaging;
    private $firestore;

    private $topic;
    private $CI;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(__DIR__ . '/secret/nicavizaje-31f01-firebase-adminsdk-cuhtp-6af8709e2a.json');
        $this->auth = $factory->createAuth();
        $this->messaging = $factory->createMessaging();
        $this->firestore = null;
        $this->topic = 'vizaje-nica-all';
        $this->CI = &get_instance();
    }

    public function initAuth(): Auth
    {
        return $this->auth;
    }

    public function initMessaging(): Messaging
    {
        return $this->messaging;
    }

    public function initFirestore(): Firestore
    {
        if ($this->firestore === null) {
            $factory = (new Factory)
                ->withServiceAccount(
                    __DIR__ . '/secret/nicavizaje-31f01-firebase-adminsdk-cuhtp-6af8709e2a.json'
                );
    
            $this->firestore = $factory->createFirestore();
        }
    
        return $this->firestore;
    }

    /************************************************/

    public function addDocument($table, $key, $data): bool
    {
        $users = $this->firestore->database()->collection($table);
        $document = $users->document($key);
        $document->create($data);

        return true;
    }
    public function updateDocument($table, $key, $data): bool
    {
        $users = $this->firestore->database()->collection($table);
        $document = $users->document($key);
        foreach ($data as $key => $value) {
            $document->update([
                ['path' => new FieldPath([$key]), 'value' => $value]
            ]);
        }

        return true;
    }
    public function getDocument($table, $key)
    {
        $firestore = $this->initFirestore();
    
        $docRef = $firestore
            ->database()
            ->collection($table)
            ->document($key);
    
        $snapshot = $docRef->snapshot();
    
        if ($snapshot->exists()) {
            return $snapshot->data();
        }
    
        return false;
    }
    public function findDocument($table, $col, $value)
    {
        $rows = $this->firestore->database()->collection($table);
        $query = $rows->where($col, '=', $value);
        $snapshot = $query->documents();
        $data = false;
        foreach ($snapshot as $item) {
            $data = $item->data();
        }

        return $data;
    }
    public function allDocument($table)
    {
        $rows = $this->firestore->database()->collection($table);
        $snapshot = $rows->documents();
        $data = [];
        $date = date('Y-m-d', strtotime('- 2 days')) . 'T00:00:00.000000Z';
        foreach ($snapshot as $item) {
            if ($item->createTime()->formatAsString() > $date) {
                $data[$item->id()] = $item->data();
            }
        }

        return $data;
    }

    /*************************************************/

    public function subscribeToTopic($tokens, $topic = false)
    {
        $this->messaging->subscribeToTopic(($topic) ? $topic : $this->topic, $tokens);
    }

    public function unsubscribeFromTopic($tokens, $topic = false)
    {
        $this->messaging->unsubscribeFromTopic(($topic) ? $topic : $this->topic, $tokens);
    }

    /**
     * @throws FirebaseException
     */
    public function getAppInstance($token): Messaging\AppInstance
    {
        return $this->messaging->getAppInstance($token);
    }

    /*************************************************/
    public function createUserByPhoneNumber($data)
    {
        try {
            return $this->auth->createUser($data);
        } catch (PhoneNumberExists $e) {
            return $this->auth->getUserByPhoneNumber($data['phoneNumber']);
        } catch (UserNotFound | AuthException | FirebaseException $e) {
            $this->CI->db->insert("vsl_log", ['phone_number' => $data['phoneNumber'], 'location' => 'add firebase', 'json' => json_encode($e->getMessage())]);
            return false;
        }
    }

    public function createCustomToken($uid): string
    {
        return $this->auth->createCustomToken($uid)->toString();
    }
    public function getUserByPhoneNumber($phoneNumber)
    {
        try {
            return $this->auth->getUserByPhoneNumber($phoneNumber);
        } catch (AuthException $e) {
        } catch (FirebaseException $e) {
            return false;
        }

        return false;
    }

    public function sendPush($alert, $data, $token = false): array
    {
        $message = CloudMessage::withTarget(
            ($token) ? "token" : "topic",
            ($token) ? $token : $this->topic
        )
            ->withNotification(Notification::create($alert['title'], $alert['body']))
            ->withApnsConfig(
                [
                    'headers' => [
                        'apns-priority' => '10'
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $alert['title'],
                                'body' => $alert['body'],
                            ],
                            'badge' => 1,
                        ],
                    ],
                ]
            )
            ->withAndroidConfig([
                'ttl' => '3600s',
                'priority' => 'normal',
                'notification' => [
                    'title' => $alert['title'],
                    'body' => $alert['body'],
                    'icon' => $alert['image'] ? $alert['image'] : 'ic_launcher_foreground',
                    'color' => '#ffffff',
                    'sound' => 'bingbong.aiff'
                ],
            ])
            ->withData($data);

        return $this->messaging->send($message);
    }
}
