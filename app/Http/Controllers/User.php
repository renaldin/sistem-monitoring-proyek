<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModelUser;
use Illuminate\Support\Facades\Hash;

class User extends Controller
{

    private $ModelUser, $ModelSiteDetail, $ModelSite, $public_path;

    public function __construct()
    {
        $this->ModelUser = new ModelUser();
        $this->public_path = 'photo';
    }

    public function index()
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }

        $data = [
            'title'             => 'Data Pengguna',
            'subTitle'          => 'Daftar Pengguna',
            'daftarUser'        => $this->ModelUser->findAll('id_user', 'DESC'),
            'user'              => $this->ModelUser->findOne('id_user', Session()->get('id_user')),
        ];

        return view('user.index', $data);
    }

    public function detail($id_user)
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }

        $data = [
            'title'             => 'Data Pengguna',
            'subTitle'          => 'Detail Pengguna',
            'detail'            => $this->ModelUser->findOne('id_user', $id_user),
            'user'              => $this->ModelUser->findOne('id_user', Session()->get('id_user')),
            'form'              => 'Detail'
        ];

        return view('user.form', $data);
    }

    public function new()
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }

        if(!Request()->fullname) {
            $data = [
                'title'     => 'Data Pengguna',
                'subTitle'  => 'Tambah Pengguna',
                'user'      => $this->ModelUser->findOne('id_user', Session()->get('id_user')),
                'form'      => 'Tambah',
            ];
            return view('user.form', $data);
        } else {
            Request()->validate([
                'fullname'      => 'required',
                'username'      => 'required',
                'email'         => 'required|unique:user,email',
                'password'      => 'min:6|required',
                'user_address'  => 'required',
                'mobile_phone'  => 'required',
                'role'          => 'required',
                'photo'         => 'required|mimes:jpeg,png,jpg|max:2048'
            ], [
                'fullname.required'     => 'Nama lengkap harus diisi!',
                'username.required'     => 'Username harus diisi!',
                'email.required'        => 'Email harus diisi!',
                'email.unique'          => 'Email sudah digunakan!',
                'password.required'     => 'Password harus diisi!',
                'password.min'          => 'Password minikal 6 karakter!',
                'user_address.required' => 'Alamat harus diisi!',
                'mobile_phone.required' => 'Nomor telepon harus diisi!',
                'role.required'         => 'Role harus diisi!',
                'photo.required'        => 'Foto harus diisi!',
                'photo.mimes'           => 'Format Foto harus jpg/jpeg/png!',
                'photo.max'             => 'Ukuran Foto maksimal 2 mb',
            ]);

            $file = Request()->photo;
            $fileName = date('mdYHis') . ' ' . Request()->fullname . '.' . $file->extension();
            $file->move(public_path($this->public_path), $fileName);

            $data = [
                'fullname'      => Request()->fullname,
                'username'      => Request()->username,
                'email'         => Request()->email,
                'password'      => Hash::make(Request()->password),
                'user_address'  => Request()->user_address,
                'mobile_phone'  => Request()->mobile_phone,
                'role'          => Request()->role,
                'photo'         => $fileName
            ];
    
            $this->ModelUser->create($data);
            return redirect()->route('daftar-pengguna')->with('success', 'Data berhasil ditambahkan!');
        }
    }

    public function update($id_user)
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }

        if(!Request()->fullname) {
            $data = [
                'title'         => 'Data Pengguna',
                'subTitle'      => 'Edit Pengguna',
                'form'          => 'Edit',
                'user'          => $this->ModelUser->findOne('id_user', Session()->get('id_user')),
                'detail'        => $this->ModelUser->findOne('id_user', $id_user)
            ];
            return view('user.form', $data);
        } else {
            Request()->validate([
                'fullname'      => 'required',
                'username'      => 'required',
                'email'         => 'required',
                'user_address'  => 'required',
                'mobile_phone'  => 'required',
                'role'          => 'required',
                'photo'         => 'mimes:jpeg,png,jpg|max:2048'
            ], [
                'fullname.required'     => 'Nama lengkap harus diisi!',
                'username.required'     => 'Username harus diisi!',
                'email.required'        => 'Email harus diisi!',
                'user_address.required' => 'Alamat harus diisi!',
                'mobile_phone.required' => 'Nomor telepon harus diisi!',
                'role.required'         => 'Role harus diisi!',
                'photo.mimes'           => 'Format Foto harus jpg/jpeg/png!',
                'photo.max'             => 'Ukuran Foto maksimal 2 mb',
            ]);

            $user = $this->ModelUser->findOne('id_user', $id_user);

            $data = [
                'id_user'       => $id_user,
                'fullname'      => Request()->fullname,
                'username'      => Request()->username,
                'email'         => Request()->email,
                'user_address'  => Request()->user_address,
                'mobile_phone'  => Request()->mobile_phone,
                'role'          => Request()->role
            ];

            if (Request()->password) {
                $data['password'] = Hash::make(Request()->password);
            }

            if (Request()->photo <> "") {
                if ($user->photo <> "") {
                    unlink(public_path($this->public_path) . '/' . $user->photo);
                }

                $file = Request()->photo;
                $fileName = date('mdYHis') . Request()->fullname . '.' . $file->extension();
                $file->move(public_path($this->public_path), $fileName);

                $data['photo'] = $fileName;
            }
            
            $this->ModelUser->edit($data);
            return redirect()->route('daftar-pengguna')->with('success', 'Data berhasil diedit!');
        }
    }

    public function delete($id_user)
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }
        
        $user = $this->ModelUser->findOne('id_user', $id_user);

        if ($user->photo <> "") {
            unlink(public_path($this->public_path) . '/' . $user->photo);
        }

        $this->ModelUser->deleteData('id_user', $id_user);
        return back()->with('success', 'Data berhasil dihapus !');
    }

    public function profil($id_user = null)
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }

        if(!Request()->fullname) {
            $data = [
                'title'     => 'Profil',
                'subTitle'  => 'Edit Profil',
                'user'      => $this->ModelUser->findOne('id_user', Session()->get('id_user'))
            ];
            return view('profil.index', $data);
        } else {
            Request()->validate([
                'fullname'      => 'required',
                'username'      => 'required',
                'email'         => 'required',
                'user_address'  => 'required',
                'mobile_phone'  => 'required',
                'photo'         => 'mimes:jpeg,png,jpg|max:2048'
            ], [
                'fullname.required'     => 'Nama lengkap harus diisi!',
                'username.required'     => 'Username harus diisi!',
                'email.required'        => 'Email harus diisi!',
                'user_address.required' => 'Alamat harus diisi!',
                'mobile_phone.required' => 'Nomor telepon harus diisi!',
                'photo.mimes'           => 'Format Foto harus jpg/jpeg/png!',
                'photo.max'             => 'Ukuran Foto maksimal 2 mb',
            ]);

            $user = $this->ModelUser->findOne('id_user', $id_user);

            $data = [
                'id_user'       => $id_user,
                'fullname'      => Request()->fullname,
                'username'      => Request()->username,
                'email'         => Request()->email,
                'user_address'  => Request()->user_address,
                'mobile_phone'  => Request()->mobile_phone
            ];

            if (Request()->photo <> "") {
                if ($user->photo <> "") {
                    unlink(public_path($this->public_path) . '/' . $user->photo);
                }

                $file = Request()->photo;
                $fileName = date('mdYHis') . Request()->fullname . '.' . $file->extension();
                $file->move(public_path($this->public_path), $fileName);
            
                $data['photo'] = $fileName;
            }

            $this->ModelUser->edit($data);
            return back()->with('success', 'Profil berhasil diedit!');
        }
    }

    public function changePassword($id_user = null)
    {
        if (!Session()->get('role')) {
            return redirect()->route('login');
        }

        if(!Request()->password_lama) {
            $data = [
                'title'     => 'Profil',
                'subTitle'  => 'Ubah Password',
                'user'      => $this->ModelUser->findOne('id_user', Session()->get('id_user'))
            ];
            return view('profil.ubahPassword', $data);
        } else {
            Request()->validate([
                'password_lama'     => 'required|min:6',
                'password_baru'     => 'required|min:6',
            ], [
                'password_lama.required'    => 'Password Lama harus diisi!',
                'password_lama.min'         => 'Password Lama minikal 6 karakter!',
                'password_baru.required'    => 'Password Baru harus diisi!',
                'password_baru.min'         => 'Password Lama minikal 6 karakter!',
            ]);
    
            $user = $this->ModelUser->findOne('id_user', $id_user);
    
            if (Hash::check(Request()->password_lama, $user->password)) {
                $data = [
                    'id_user'         => $id_user,
                    'password'        => Hash::make(Request()->password_baru)
                ];
    
                $this->ModelUser->edit($data);
                return back()->with('success', 'Password berhasil diubah !');
            } else {
                return back()->with('failed', 'Password lama tidak sesuai.');
            }
        }

    }

    // public function changePasswordProcess($id_user)
    // {
    //     Request()->validate([
    //         'password_lama'     => 'required|min:6',
    //         'password_baru'     => 'required|min:6',
    //     ], [
    //         'password_lama.required'    => 'Password Lama harus diisi!',
    //         'password_lama.min'         => 'Password Lama minikal 6 karakter!',
    //         'password_baru.required'    => 'Password Baru harus diisi!',
    //         'password_baru.min'         => 'Password Lama minikal 6 karakter!',
    //     ]);

    //     $user = $this->ModelUser->detail($id_user);

    //     if (Hash::check(Request()->password_lama, $user->password)) {
    //         $data = [
    //             'id_user'         => $id_user,
    //             'password'         => Hash::make(Request()->password_baru)
    //         ];

    //         $this->ModelUser->edit($data);
    //         return back()->with('success', 'Password berhasil diubah !');
    //     } else {
    //         return back()->with('failed', 'Password Lama tidak sesuai.');
    //     }
    // }
}
