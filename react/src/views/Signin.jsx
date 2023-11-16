import {Link} from 'react-router-dom';
import axiosClient from '../axios-client.js';
import {createRef} from 'react';
import {useStateContext} from '../context/ContextProvider.jsx';
import { useState } from 'react';
import Button from '../components/Button';
import Input from '../components/Input';
import H1 from '../components/H1';


export default function Signin() {
  const emailRef = createRef()
  const passwordRef = createRef()
  const { setUser, setToken } = useStateContext()
  const [message, setMessage] = useState(null)

  const initialState = {
    email: '',
    password: '',
  };
  const [state, setState] = useState(initialState);

  const handleOnchange = (name, value) => {
    let newState = {
      ...state,
      [name]: value
    }
    setState(newState);
  }

  const onSubmit = (ev) => {
    ev.preventDefault()

    axiosClient.post('/signin', state)
      .then(({data}) => {
        setUser(data.user)
        setToken(data.token);
      })
      .catch((err) => {
        const response = err.response;
        if (response && response.status === 422) {
          setMessage(response.data.message)
        }
      })
  }

  return (
    <div className="h-[100vh] flex justify-center items-center">
      <div className="w-[360px] relative z-1 bg-white/40 p-6 shadow">
        <form onSubmit={onSubmit}>
          <H1>Sign in into your account</H1>
          <Input value={state.email} type="email" placeholder="Email" onChange={event => handleOnchange('email', event.currentTarget.value)} />
          <Input value={state.password} type="password" placeholder="Password"  onChange={event => handleOnchange('password', event.currentTarget.value)} />
          {message &&
            <div className="text-rose-600">
              <p>{message}</p>
            </div>
          }
          <Button>Sign In</Button>
          <p className="text-slate-400 mt-4">Not registered? <Link to="/signup" className="text-slate-700">Create an account</Link></p>
        </form>
      </div>
    </div>
  )
}
