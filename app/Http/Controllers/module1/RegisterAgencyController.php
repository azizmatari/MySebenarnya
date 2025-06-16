<?php

namespace App\Http\Controllers\module1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\module1\Agency;

class RegisterAgencyController extends Controller
{
    // Show the registration form
    public function showRegisterForm()
    {
        $agencyTypes = Agency::getAgencyTypes();
        return view('module1.RegisterAgencyView', compact('agencyTypes'));
    }

    // Handle the registration POST
    public function register(Request $request)
    {
        $request->validate([
            'agency_name' => 'required|string|max:50',
            'agencyUsername' => 'required|string|max:20|unique:agency,agencyUsername',
            'agencyPassword' => 'required|string|min:6|confirmed',
            'agencyType' => 'required|in:Education,Police,Sports,Health',
            // 'mcmcId' is not needed in validation anymore
        ]);
        Agency::create([
            'agency_name' => $request->agency_name,
            'agencyUsername' => $request->agencyUsername,
            'agencyPassword' => Hash::make($request->agencyPassword),
            'agencyType' => $request->agencyType,
            'mcmcId' => session('user_id'), // Set from session
            'first_login' => true, // Flag as first login
        ]);

        return redirect()->route('register.agency.view')->with('success', 'Agency registered successfully!');
    }
    /**
     * COMMENTED OUT - Add a new agency type
     * For future use with dynamic agency types
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /*
    public function addAgencyType(Request $request)
    {
        $request->validate([
            'agencyType' => 'required|string|max:50',
        ]);

        $newType = $request->agencyType;
        $currentTypes = Agency::getAgencyTypes();

        // Check if type already exists
        if (in_array($newType, $currentTypes)) {
            return redirect()->back()->with('type_error', 'Agency type already exists');
        }

        // Add the new type to the array
        $currentTypes[] = $newType;

        // Update the getAgencyTypes method in Agency model
        $this->updateAgencyTypesInModel($currentTypes);

        return redirect()->back()->with('type_success', 'Agency type added successfully');
    }
    */

    /**
     * COMMENTED OUT - Update the getAgencyTypes method in Agency model
     * For future use with dynamic agency types
     *
     * @param array $types
     * @return void
     */
    /*    
    private function updateAgencyTypesInModel(array $types)
    {
        return Agency::saveAgencyTypes($types);
    }
    */

    /**
     * COMMENTED OUT - Reset agency types to default values
     * For future use with dynamic agency types
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    /*
    public function resetAgencyTypes()
    {
        // Delete the agency_types.json file to reset to default types
        $typesFile = storage_path('app/agency_types.json');
        if (file_exists($typesFile)) {
            unlink($typesFile);
        }

        return redirect()->back()->with('type_success', 'Agency types have been reset to default values');
    }
    */
}
