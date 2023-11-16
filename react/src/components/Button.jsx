export default function Button({...props}) {

  return (
      <button type="submit" className="bg-slate-600 text-white p-3 cursor-pointer block w-[100%]">{props.children}</button>
  )
}
