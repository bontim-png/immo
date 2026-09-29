<?php
namespace App\Controllers;

use App\Models\Property;
use App\Models\Office;
use App\Models\Agent;
use App\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $user = $this->getUser();
        
        $propertyModel = new Property();
        $officeModel = new Office();
        $agentModel = new Agent();

        // Get counts
        if ($this->isAdmin()) {
            $totalProperties = $propertyModel->count();
            $totalOffices = $officeModel->count();
            $totalAgents = $agentModel->count();
            
            $recentProperties = $propertyModel->query(
                "SELECT * FROM properties ORDER BY created_at DESC LIMIT 5"
            );
        } else {
            $officeId = $this->getUserOfficeId();
            $totalProperties = count($propertyModel->getByOffice($officeId));
            $totalAgents = count($agentModel->getAgentsByOffice($officeId));
            $totalOffices = 1; // Only their office
            
            $recentProperties = $propertyModel->getByOffice($officeId);
            $recentProperties = array_slice($recentProperties, 0, 5);
        }

        $this->view('dashboard.index', [
            'totalProperties' => $totalProperties,
            'totalOffices' => $totalOffices,
            'totalAgents' => $totalAgents,
            'recentProperties' => $recentProperties,
            'user' => $user,
        ]);
    }
}
