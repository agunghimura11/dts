<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\Company;
use App\Models\Postcode;
use App\Models\Prefecture;
use Config;

class CompanyController extends Controller
{
    /**
     * Get named route
     *
     */
    private function getRoute() {
        return 'company';
    }

    /**
     * Validator for user
     *
     * @return \Illuminate\Http\Response
     */
    protected function validator(array $data, $type) {
        if ($type == 'update') {
            return Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|string|max:255',
                    'postcode' => 'required|string|max:255',
                    'prefecture_id' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'local' => 'required|string|max:255'
            ]);
        }
        return Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|string|max:255',
                'postcode' => 'required|string|max:255',
                'prefecture_id' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'local' => 'required|string|max:255',
                'image' => 'required'
        ]);
    }

    public function index() {
        return view('backend.companies.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function geDataPostcode ($id) {
        $data = Postcode::where('postcode',$id)->first();

        return response()->json($data, 200);
    }

    public function add()
    {
        $prefectures = Prefecture::pluck('display_name','id');
        $prefectures[''] = '';
        $company = new Company();
        $company->form_action = $this->getRoute() . '.create';
        $company->page_title = 'Company Add Page';
        $company->page_type = 'create';
        $company->prefectures = $prefectures;
        
        return view('backend.companies.form', [
            'company' => $company
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $data = $request->all();
        // Validate input, indicate this is 'create' function
        $this->validator($data, 'create')->validate();

        try {
            $company = Company::create($data);
            if ($company) {
                // Create is successful, back to list
                $file = $request->file('image');
                // nama file
                echo 'File Name: '.$file->getClientOriginalName();
                echo '<br>';
                // ekstensi file
                echo 'File Extension: '.$file->getClientOriginalExtension();
                echo '<br>';
                // real path
                echo 'File Real Path: '.$file->getRealPath();
                echo '<br>';
                // ukuran file
                echo 'File Size: '.$file->getSize();
                echo '<br>';
                // tipe mime
                echo 'File Mime Type: '.$file->getMimeType();
                echo '<br>';
                // isi dengan nama folder tempat kemana file diupload
                $tujuan_upload = './uploads/files/';
                $filename = 'image_'.$company->id.'.'.$file->getClientOriginalExtension();
                $upload = $file->move($tujuan_upload,$filename);
                $company->update(['image'=>$filename]);
            
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            } else {
                // Create is failed
                return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
            }
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $prefectures = Prefecture::pluck('display_name','id');
        $prefectures[''] = '';
        $company = Company::where('id',$id)->first();
        $company->form_action = $this->getRoute() . '.update';
        $company->page_title = 'Company Edit Page';
        $company->prefectures = $prefectures;
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $company->page_type = 'edit';
        return view('backend.companies.form', [
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        $data = $request->all();
        try {
            $currentCompany = Company::find($request->get('id'));
            if ($currentCompany) {
                $this->validator($data, 'update')->validate();
                $file = $request->file('image');
                if ($file !== null) {
                    // Create is successful, back to list
                    // nama file
                    echo 'File Name: '.$file->getClientOriginalName();
                    echo '<br>';
                    // ekstensi file
                    echo 'File Extension: '.$file->getClientOriginalExtension();
                    echo '<br>';
                    // real path
                    echo 'File Real Path: '.$file->getRealPath();
                    echo '<br>';
                    // ukuran file
                    echo 'File Size: '.$file->getSize();
                    echo '<br>';
                    // tipe mime
                    echo 'File Mime Type: '.$file->getMimeType();
                    echo '<br>';
                    // isi dengan nama folder tempat kemana file diupload
                    $tujuan_upload = './uploads/files/';
                    $filename = 'image_'.$request->get('id').'.'.$file->getClientOriginalExtension();
                    $upload = $file->move($tujuan_upload,$filename);
                    // Update data
                    $data['image'] = $filename;
                }
                $currentCompany->update($data);
                // If update is successful
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            } else {
                // If update is failed
                return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
            }
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        }
    }

    public function delete(Request $request) {
        try {
            // Get user by id
            $company = Company::find($request->get('id'));
            // If to-delete user is not the one currently logged in, proceed with delete attempt
            if ($company) {
                $rootfolder = './uploads/files/';
                $filename = $rootfolder.'/'.$company['image'];
                File::delete($filename);
                // Delete data
                $company->delete();

                // If delete is successful
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
            }
            // Send error if logged in user trying to delete himself
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_SELF_MESSAGE'));
        } catch (Exception $e) {
            // If delete is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        }
    }
}
