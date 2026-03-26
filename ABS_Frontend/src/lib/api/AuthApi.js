import axios from "axios"

export const logout =async (token)=>{
return await axios.post('/api/logout',{
  token
})
}