import {Link, Navigate, Outlet} from 'react-router-dom';
import {useStateContext} from '../context/ContextProvider';
import axiosClient from '../axios-client.js';
import {useEffect} from 'react';

export default function DefaultLayout() {
  const {user, token, setUser, setToken, notification} = useStateContext();

  if (!token) {
    return <Navigate to="/signin"/>
  }

  const onLogout = ev => {
    ev.preventDefault()

    axiosClient.post('/signout')
      .then(() => {
        setUser({})
        setToken(null)
      })
  }

  useEffect(() => {
    axiosClient.get('/user')
      .then(({data}) => {
         setUser(data)
      })
  }, [])

  return (
    <div id="defaultLayout" className="flex">
      <aside className="width=[240px] bg-slate-600 p-6">
        <p className="mb-3"><Link to="/upload" className="text-white hover:text-slate-300">Upload a document</Link></p>
        <p><Link to="/results" className="text-white hover:text-slate-300">Verification results</Link></p>
      </aside>

      <div className="flex-1">
        <header className="flex justify-between items-center height-[80px] p-6 bg-white shadow">
          <div>
            Document verification
          </div>
          <div>
            {user.name} &nbsp; | &nbsp; <a onClick={onLogout} href="#">Logout</a>
          </div>
        </header>

        <main className="p-3">
          <Outlet/>
        </main>
      </div>
    </div>
  )
}
