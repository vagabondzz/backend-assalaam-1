import api from "./axios.js";

export async function login(email, password) {
  try {
    const response = await api.post("/login", {
      email,
      password
    });

    // Simpan token ke localStorage
    localStorage.setItem("token", response.data.token);
    console.log("Login berhasil:", response.data);
  } catch (error) {
    console.error("Login gagal:", error.response?.data || error.message);
  }
}

export async function getUserProfile() {
    try {
      const token = localStorage.getItem("token");
  
      const response = await api.get("/user", {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
  
      console.log("Profil user:", response.data);
    } catch (error) {
      console.error("Gagal ambil profil:", error.response?.data || error.message);
    }
  }