<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Set owner type once per session for global UI use.
        if (
            auth()->loggedIn()
            && ! session()->has('owner_type')
        ) {
            $userId = (int) (auth()->id() ?? 0);

            if ($userId > 0) {
                $db = db_connect();

                $agentLink = null;

                if ($db->tableExists('agent_users')) {
                    $agentLink = $db
                        ->table('agent_users AS au')
                        ->select('au.id')
                        ->join(
                            'agents AS a',
                            'a.id = au.agent_id',
                            'inner'
                        )
                        ->where('au.user_id', $userId)
                        ->where('au.status', 1)
                        ->where('a.status', 1)
                        ->limit(1)
                        ->get()
                        ->getRowArray();
                }

                session()->set(
                    'owner_type',
                    $agentLink ? 'AGENT' : 'ADMINISTRATOR'
                );
            }
        }

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Admin pusat saja yang boleh memilih account owner
     * secara bebas. Agent, white label, dan customer harus
     * terkunci ke profil masing-masing.
     *
     * User yang punya grup admin tetapi juga terhubung ke
     * profil agent dianggap staf/agent, bukan admin pusat.
     */
    protected function canChooseAnyOwner(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ($user->inGroup('superadmin')) {
            return true;
        }

        if (!$user->inGroup('admin')) {
            return false;
        }

        $userId = (int) (auth()->id() ?? 0);

        if ($userId <= 0) {
            return false;
        }

        $db = db_connect();

        if ($db->tableExists('agent_users')) {
            $agentLink = $db
                ->table('agent_users AS au')
                ->select('au.id')
                ->join(
                    'agents AS a',
                    'a.id = au.agent_id',
                    'inner'
                )
                ->where('au.user_id', $userId)
                ->where('au.status', 1)
                ->where('a.status', 1)
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($agentLink) {
                return false;
            }
        }

        return true;
    }
}
